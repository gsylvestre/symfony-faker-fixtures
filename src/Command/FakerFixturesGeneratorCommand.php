<?php

namespace FakerFixtures\Command;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use FakerFixtures\Doctrine\AssociationHelper;
use FakerFixtures\Doctrine\DependencyGraph;
use FakerFixtures\Doctrine\FieldDataExtractor;
use FakerFixtures\Security\UserClassHelper;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\SchemaValidator;
use FakerFixtures\File\FileManager;


/**
 * Class FakerFixturesGeneratorCommand
 * @package FakerFixtures\Command
 */
class FakerFixturesGeneratorCommand extends AbstractMaker
{

    /** @var DoctrineHelper */
    private $entityHelper;

    /** @var UserClassHelper */
    private $userClassHelper;

    /** @var array */
    private $commandNames = [];

    /** @var ConsoleStyle */
    private $io;

    //OMG i didnt do that
    /** @TODO this will break hard */
    const PATH_TO_SKELETONS = '../../../../../gsylvestre/symfony-faker-fixtures/src/Resources/skeleton/command/';

    /**
     * FakerFixturesGeneratorCommand constructor.
     * @param DoctrineHelper $entityHelper
     * @param FileManager $fileManager
     */
    public function __construct(DoctrineHelper $entityHelper, FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
        $this->entityHelper = $entityHelper;
        $this->userClassHelper = new UserClassHelper($fileManager);
    }

    /**
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:faker-fixtures';
    }

    /**
     * @param Command $command
     * @param InputConfiguration $inputConf
     * @return void
     */
    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->setDescription('Generates a new command to load data with Faker')
            ->addOption(
                'delete-previous',
                null,
                InputOption::VALUE_NONE,
                'Destroy previous faker fixtures?'
            )
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_OPTIONAL,
                'Faker locale?',
                'en_US'
            )
        ;
    }

    /**
     *
     * Generate fixtures commands
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     * @throws \Exception
     * @return void
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $this->io = $io;

        if ($input->getOption('delete-previous')){
            $this->fileManager->deletePreviousFixture($io);
        }

        $fakerLocale = $input->getOption('locale');

        $em = $this->entityHelper->getRegistry()->getManager();

        $this->validateSchema($em);

        $metas = $em->getMetadataFactory()->getAllMetadata();

        $this->generateMetaFixtureClass($metas, $generator, $fakerLocale);

        $this->writeSuccessMessage($io);

        $io->comment([
            'Next:',
            '1. Check your new fixtures Command in src\\Command\\FakerFixturesCommand.php',
            '2. Feel free to edit them, they are yours!',
            '3. Run "php bin/console app:fixtures:load" to load em all'
        ]);
    }

    private function validateSchema($em)
    {
        //check for schema validation errors
        $schemaValidator = new SchemaValidator($em);
        $schemaValidationErrors = $schemaValidator->validateMapping();
        if (count($schemaValidationErrors) > 0) {
            $this->io->error("You have errors in your entities! Please run 'php bin/console doctrine:schema:validate' to find & fix them first.");
            die("See you later.");
        }

        //check for database synchronicity errors
        $schemaIsInSync = $schemaValidator->schemaInSyncWithMetadata();
        if (!$schemaIsInSync) {
            $this->io->error("You database is not in sync with your entities! Please run your latest migrations or run 'php bin/console doctrine:schema:update --force'");
            die("See you later.");
        }
    }

    /**
     *
     * Generates a meta fixtures class, that will load every entity fixtures
     *
     * @param array $metas
     * @param Generator $generator
     * @throws \Exception
     * @return void
     */
    private function generateMetaFixtureClass(array $metas, Generator $generator, string $fakerLocale): void
    {
        //generated command details
        $commandName = "app:fixtures:load";
        $commandClassNameDetails = $generator->createClassNameDetails(
            "FakerFixtures",
            'Command',
            'Command',
            sprintf('The "%s" command name is not valid because it would be implemented by "%s" class, which is not valid as a PHP class name (it must start with a letter or underscore, followed by any number of letters, numbers, or underscores).', $commandName, Str::asClassName($commandName, 'Command'))
        );

        //which entity to load first ? based on entities relations
        $em = $this->entityHelper->getRegistry()->getManager();
        $dependencyGraph = new DependencyGraph($metas, $em);

        $inflector = new Inflector();

        $entitiesData = [];
        /** @var ClassMetadata $classMetaData */
        foreach($dependencyGraph->getDependencyGraph() as $classMetaData){
            //helps get infos about each field
            $fieldDataExtractor = new FieldDataExtractor();

            //are we generating the class used with Security?
            $securityUserClass = null;
            if ($this->userClassHelper->isSecurityUserClass($classMetaData->getName())) {
                $securityUserClass = $this->userClassHelper->getUserClassInfos();
            }

            $classData = $fieldDataExtractor->getFieldsData($classMetaData, $securityUserClass);

            $entitiesData[] = $classData;
        }

        $generator->generateClass(
            $commandClassNameDetails->getFullName(),
            self::PATH_TO_SKELETONS . 'MetaFakerFixtures.tpl.php',
            [
                'command_name' => $commandName,
                'command_class_name' => 'FakerFixturesCommand',
                "class_datas" => $entitiesData,
                'faker_locale' => $fakerLocale,
            ]
        );

        $generator->writeChanges();
    }

    /**
     * @param DependencyBuilder $dependencies
     * @return void
     */
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(
            Command::class,
            'console'
        );
        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm',
            false
        );
    }
}
<?php

namespace FakerFixtures\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use FakerFixtures\Doctrine\DepencyGraph;
use FakerFixtures\Doctrine\FieldDataExtractor;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Doctrine\ORM\Tools\SchemaValidator;


/**
 * Class FakerFixturesGeneratorCommand
 * @package FakerFixtures\Command
 */
class FakerFixturesGeneratorCommand extends AbstractMaker
{

    /** @var DoctrineHelper */
    private $entityHelper;

    /** @var int */
    private $time;

    /** @var array */
    private $commandNames = [];

    //OMG i didnt do that
    /** @TODO this will break hard */
    const PATH_TO_SKELETONS = '../../../../../gsylvestre/symfony-faker-fixtures/src/Resources/skeleton/command/';

    /**
     * FakerFixturesGeneratorCommand constructor.
     * @param DoctrineHelper $entityHelper
     */
    public function __construct(DoctrineHelper $entityHelper, FileManager $fileManager)
    {
        $this->time = time();
        $this->fileManager = $fileManager;
        $this->entityHelper = $entityHelper;
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
            );
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
        if ($input->getOption('delete-previous')){
            $this->deletePreviousFixtures($io);
        }

        $em = $this->entityHelper->getRegistry()->getManager();

        //check for schema validation errors
        $schemaValidator = new SchemaValidator($em);
        $schemaValidationErrors = $schemaValidator->validateMapping();
        if (count($schemaValidationErrors) > 0) {
            $io->error("You have errors in your entities! Please run 'php bin/console doctrine:schema:validate' to find/fix them first.");
            die("See you later.");
        }

        $metas = $em->getMetadataFactory()->getAllMetadata();

        foreach ($metas as $meta) {
            $entityFullName = $meta->getName();
            $this->generateEntityFixtureClass($entityFullName, $input, $io, $generator);
        }

        $this->generateMetaFixtureClass($metas, $generator);

        $this->writeSuccessMessage($io);

        $io->comment([
            'Next:',
            '1. Check your new fixtures in src\\Command\\FakerFixtures',
            '2. Feel free to edit them, they are yours!',
            '3. Run "php bin/console app:fixtures:load-all" to load em all'
        ]);
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
    private function generateMetaFixtureClass(array $metas, Generator $generator): void
    {
        //generated command details
        $commandName = "app:fixtures:load-all";
        $commandClassNameDetails = $generator->createClassNameDetails(
            "LoadAllFixtures",
            'Command\\FakerFixtures',
            'Command',
            sprintf('The "%s" command name is not valid because it would be implemented by "%s" class, which is not valid as a PHP class name (it must start with a letter or underscore, followed by any number of letters, numbers, or underscores).', $commandName, Str::asClassName($commandName, 'Command'))
        );

        //which entity to load first ? based on entities relations
        $depencyGraph = new DepencyGraph($metas);
        $orderedClassesInfos = $depencyGraph->getOrder();

        $generator->generateClass(
            $commandClassNameDetails->getFullName(),
            self::PATH_TO_SKELETONS . 'MetaFakerFixtures.tpl.php',
            [
                'command_name' => $commandName,
                'class_infos' => $orderedClassesInfos,
                'sub_command_names' => $this->commandNames,
            ]
        );

        $generator->writeChanges();
    }

    /**
     *
     * Generates a single entity fixture class
     *
     * @param string $entityFullName
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     * @throws \ReflectionException
     * @return void
     */
    private function generateEntityFixtureClass(string $entityFullName, InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        //short class name, useful for variables in command...
        $boundClass = (new \ReflectionClass($entityFullName))->getShortName();

        //generated command details
        $commandName = "app:fixtures:".mb_strtolower($boundClass);
        //$boundClass = trim($input->getArgument('bound-class'));
        $commandClassNameDetails = $generator->createClassNameDetails(
            $boundClass."Fixture",
            'Command\\FakerFixtures',
            'Command',
            sprintf('The "%s" command name is not valid because it would be implemented by "%s" class, which is not valid as a PHP class name (it must start with a letter or underscore, followed by any number of letters, numbers, or underscores).', $commandName, Str::asClassName($commandName, 'Command'))
        );

        //remember all generated command names
        $this->commandNames[$entityFullName] = $commandName;

        /** @var ObjectManager $em */
        $em = $this->entityHelper->getRegistry()->getManager();

        //get entity Doctrine metadata
        $boundClassDetails = $generator->createClassNameDetails(
            $boundClass,
            'Entity\\'
        );
        $classMetaData = $em->getClassMetadata($boundClassDetails->getFullName());

        //helps get infos about each field
        $fieldDataExtractor = new FieldDataExtractor();

        $generator->generateClass(
            $commandClassNameDetails->getFullName(),
            self::PATH_TO_SKELETONS . 'EntityFakerFixtures.tpl.php',
            [
                'command_name' => $commandName,
                'bound_class' => $boundClass,
                'bounded_full_class_name' => $classMetaData->getName(),
                'table_name' => $classMetaData->getTableName(),
                'pivot_table_names' => $this->getPivotTableNames($classMetaData),
                'fields' => $fieldDataExtractor->getFieldsData($classMetaData),
            ]
        );

        $generator->writeChanges();
    }

    /**
     * get all pivot table names (for truncation)
     *
     * @param ClassMetadata $classMetaData
     * @return array
     */
    private function getPivotTableNames(ClassMetadata $classMetaData): array
    {
        $names = [];
        foreach($classMetaData->getAssociationMappings() as $property => $associationInfo){
            if (!empty($associationInfo['joinTable']['name'])){
                $names[] = $associationInfo['joinTable']['name'];
            }
        }

        return $names;
    }

    /**
     * Delete all files in FakerFixtures dir
     */
    private function deletePreviousFixtures($io): void
    {
        $finder = $this->fileManager->createFinder("src/Command/FakerFixtures");

        if (!$finder->hasResults()) {
            $io->writeln("No fixtures to remove");
            return;
        }

        $foundFiles = $finder->files();
        $foundFilesNames = [];
        /** @var Symfony\Component\Finder\SplFileInfo $file */
        foreach($foundFiles as $file){
            $foundFilesNames[] = $file->getFilename();
        }

        $confirmed = $io->confirm('<bg=yellow;options=bold>Are you sure you want to delete all these files?</>' . "\n" . implode("\n", $foundFilesNames) . "\n", false);
        if (!$confirmed) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($finder);
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
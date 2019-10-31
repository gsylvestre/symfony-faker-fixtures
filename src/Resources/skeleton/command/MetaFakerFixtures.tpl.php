<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
<?php foreach($class_full_infos as $classInfo): ?>
use <?= $classInfo['full_class_name'] ?>;
<?php endforeach; ?>

class <?= $class_name; ?> extends Command
{
    protected static $defaultName = '<?= $command_name; ?>';

    /** @var SymfonyStyle */
    protected $io;
    protected $faker;
    protected $progress;
    protected $doctrine;
    protected $passwordEncoder;

    public function __construct(RegistryInterface $doctrine, UserPasswordEncoderInterface $passwordEncoder, $name = null)
    {
        parent::__construct($name);
        $this->faker = \Faker\Factory::create("<?= $faker_locale ?>");
        $this->doctrine = $doctrine;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setDescription('Load all fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->progress = new ProgressIndicator($output);
        $this->progress->start('Loading fixtures');

        $this->progress->setMessage("Truncating tables");
        $this->truncateTables();

        //order might be important
        //change argument to load more or less of each entity
<?php foreach($class_full_infos as $info): ?>
        $this->load<?= ucfirst($info['plural_name']) ?>($num = 10);
<?php endforeach; ?>

        //now loading ManyToMany data
        $this->progress->setMessage("loading many to many datas");
        $this->loadManyToManyData();

        $this->progress->finish("Done!");
        $this->io->success('Fixtures loaded!');
        return 0;
    }

<?php foreach($class_full_infos as $info): ?>
<?php include('EntityFakerFixtures.tpl.php') ?>
<?php endforeach; ?>

    protected function truncateTables()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");

<?php foreach($class_full_infos as $info): ?>
        $connection->query("TRUNCATE <?= $info['table_name'] ?>");
<?php foreach($info['pivot_table_names'] as $pivot_table_name): ?>
        $connection->query("TRUNCATE <?= $pivot_table_name ?>");
<?php endforeach; ?>
<?php endforeach; ?>

        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    protected function loadManyToManyData()
    {
<?php
$alreadyLoaded = [];
?>
<?php foreach($class_full_infos as $info): ?>
<?php foreach($info['fields'] as $field): ?>
<?php if (!empty($field['adder']) && $field['type'] === \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY && $field['isOwningSide']): ?>
<?php if (!in_array($field['assocShortClassName'], $alreadyLoaded)):
    $alreadyLoaded[] = $field['assocShortClassName'];
?>
        $all<?= $field['assocPluralName'] ?> = $this->doctrine->getRepository(<?= $field['assocShortClassName'] ?>::class)->findAll();
<?php
    endif;
    if (!in_array($info['short_class_name'], $alreadyLoaded)):
    $alreadyLoaded[] = $info['short_class_name'];
?>
        $all<?= $info['plural_name'] ?> = $this->doctrine->getRepository(<?= $info['short_class_name'] ?>::class)->findAll();
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>

<?php foreach($class_full_infos as $info): ?>
<?php foreach($info['fields'] as $field): ?>
<?php if (!empty($field['adder']) && $field['type'] === \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY && $field['isOwningSide']): ?>
<?php $methodName = sprintf($field['fakerMethod'], '$all'.$field['assocShortClassName'].'Entities'); ?>
<?php $var = "$" . lcfirst($info['short_class_name']) ?>
        //loading data in <?= $field['pivotTableName'] ?> table
        foreach($all<?= $info['plural_name'] ?> as <?= "$" . lcfirst($info['short_class_name']) ?>){
            $numberOf<?= $field['fieldName'] ?> = $this->faker->numberBetween($min = 1, $max = 5);
            //reset faker uniqueness
            $this->faker->unique(true)->randomElement([]);

            for($n = 0; $n < $numberOf<?= $field['fieldName'] ?>; $n++){
                <?= $var ?>-><?= $field['adder'] ?>( $this->faker->unique()->randomElement($all<?= $field['assocPluralName'] ?>) );
            }

            $this->doctrine->getManager()->persist(<?= $var ?>);
            $this->progress->advance();
        }

        $this->doctrine->getManager()->flush();

<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
    }
}
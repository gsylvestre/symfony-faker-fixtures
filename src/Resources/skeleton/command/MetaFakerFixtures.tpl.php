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

        $this->truncateTables();

        //order might be important
        //change argument to load more or less of each entity
<?php foreach($class_full_infos as $info): ?>
        $this->load<?= ucfirst($info['plural_name']) ?>($num = 10);
<?php endforeach; ?>

        //now loading ManyToMany data
        $this->loadManyToManyData();

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
<?php foreach($class_full_infos as $info): ?>
<?php foreach($info['fields'] as $field): ?>
<?php if (!empty($field['adder']) && $field['type'] === \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY): ?>
        for($i=0; $i<5; $i++){
            //<?= $field['adder'] ?>

        }

<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
    }
}
<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;

<?php if ($bounded_full_class_name): ?>
use <?= $bounded_full_class_name?>;
<?php endif ?>

class <?= $class_name; ?> extends Command
{
    protected static $defaultName = '<?= $command_name; ?>';

    protected $manager = null;
    protected $doctrine = null;
    protected $faker = null;

    public function __construct(RegistryInterface $doctrine, $name = null)
    {
        parent::__construct($name);
        $this->manager = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->faker = \Faker\Factory::create($locale = 'en_US');
    }

    protected function configure()
    {
        $this
        ->setDescription('Load fresh dummy data in <?= $table_name ?> table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();
<?php $num = 10; ?>
        $num = <?= $num ?>;

        for($i=0; $i<$num; $i++){
<?php $var = "$" . lcfirst($bound_class) ?>
            <?= $var ?> = new <?= $bound_class ?>();

<?php
foreach($fields as $field):
    if ($field['fieldName'] != "id"):
        if ($field['setter'] === null):
?>
            //no setter found for <?= $field['fieldName'] ?>
<?php
        elseif(empty($field['fakerMethod'])):
?>
            //no faker method found!
            //<?= $var ?>-><?= $field['setter'] ?>($this->faker-><?= $field['fakerMethod'] ?>);
<?php
        else:
?>
            <?= $var ?>-><?= $field['setter'] ?>($this->faker-><?= $field['fakerMethod'] ?>);
<?php
        endif;
    endif;
endforeach
?>

            $this->manager->persist(<?= $var ?>);
        }

        $this->manager->flush();

        $io->writeln('<?= $num ?> "<?= $bound_class ?>" loaded!');

        return 0;
    }

    protected function truncateTable()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");
        $connection->query("TRUNCATE TABLE <?= $table_name ?>");
        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}
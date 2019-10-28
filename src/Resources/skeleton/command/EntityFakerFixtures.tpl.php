<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;
<?php if ($security_user_class): ?>
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
<?php endif ?>

<?php if ($bounded_full_class_name): ?>
use <?= $bounded_full_class_name?>;
<?php endif ?>
<?php
    foreach($fields as $field):
        if ($field['isAssoc']):
?>
use <?= $field['targetEntity'] ?>;
<?php
        endif;
    endforeach;
?>

class <?= $class_name; ?> extends Command
{
    protected static $defaultName = '<?= $command_name; ?>';

    protected $manager = null;
    protected $doctrine = null;
    protected $faker = null;
<?php if ($security_user_class): ?>
    protected $passwordEncoder = null;
<?php endif; ?>

    public function __construct(RegistryInterface $doctrine<?php if ($security_user_class): ?>, UserPasswordEncoderInterface $passwordEncoder<?php endif; ?>, $name = null)
    {
        parent::__construct($name);
        $this->manager = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->faker = \Faker\Factory::create($locale = '<?= $faker_locale ?>');
<?php if ($security_user_class): ?>
        $this->passwordEncoder = $passwordEncoder;
<?php endif; ?>
    }

    protected function configure()
    {
        $this
        ->setDescription('Load fresh dummy data in <?= $table_name ?> table')
        ->addArgument('num', InputArgument::OPTIONAL, 'Load how many?', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $num = $input->getArgument('num');

        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();

<?php
foreach($fields as $field):
    if ($field['isAssoc']):
?>
        $all<?= $field['assocShortClassName'] ?>Entities = $this->doctrine->getRepository(<?= $field['assocShortClassName'] ?>::class)->findAll();
<?php
    endif;
endforeach;
?>

        for($i=0; $i<$num; $i++){
<?php $var = "$" . lcfirst($bound_class) ?>
            <?= $var ?> = new <?= $bound_class ?>();

<?php
foreach($fields as $field):
    if (!$field['isAssoc']):
        if ($field['fieldName'] != "id"):
            if (!empty($field['isSecurityPasswordField'])):
?>

            $plainPassword = "ryanryan";
            $hash = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($hash);

<?php continue; ?>
<?php endif; ?>
<?php
            if ($field['setter'] === null):
?>
            //no setter found for <?= $field['fieldName'] ?>

<?php elseif(empty($field['fakerMethod'])): ?>
            //no faker method found!
            //<?= $var ?>-><?= $field['setter'] ?>($this->faker-><?= $field['fakerMethod'] ?>);
<?php else: ?>
            <?= $var ?>-><?= $field['setter'] ?>($this->faker-><?= $field['fakerMethod'] ?>);
<?php
            endif;
        endif;
    endif;
endforeach
?>

<?php
foreach($fields as $field):
    if ($field['isAssoc']):
        $methodName = sprintf($field['fakerMethod'], '$all'.$field['assocShortClassName'].'Entities');
        if (!empty($field['adder'])):
?>
            /*
            uncomment below to add more than one
            (you might need to increase the total number of <?= $field['fieldName'] ?> to load in LoadAllFixturesCommand.php
            */
            //$numberOf<?= $field['fieldName'] ?> = $this->faker->numberBetween($min = 0, $max = 5);
            //for($n = 0; $n < $numberOf<?= $field['fieldName'] ?>; $n++){
                <?= $var ?>-><?= $field['adder'] ?>($this->faker-><?= $methodName ?>);
            //}

<?php
        elseif (!empty($field['setter'])):
?>
            <?= $var ?>-><?= $field['setter'] ?>($this->faker-><?= $methodName ?>);
<?php
        else:
?>
            //oups no method for <?= $field['fieldName'] . "\n" ?>
            //<?= $var ?>...
<?php
        endif;
    endif;
endforeach
?>

            $this->manager->persist(<?= $var ?>);
        }

        $this->manager->flush();

        $io->writeln($num . ' "<?= $bound_class ?>" loaded!');

        return 0;
    }

    protected function truncateTable()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");
        $connection->query("TRUNCATE TABLE <?= $table_name ?>");
<?php foreach($pivot_table_names as $pivot_table_name): ?>
        $connection->query("TRUNCATE TABLE <?= $pivot_table_name ?>");
<?php endforeach ?>
        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}
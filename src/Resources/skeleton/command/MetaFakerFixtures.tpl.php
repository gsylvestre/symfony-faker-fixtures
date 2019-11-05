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
<?php
/** @var \FakerFixtures\Doctrine\ClassData $class_data */
foreach($class_datas as $class_data): ?>
use <?= $class_data->getFullClassName() ?>;
<?php endforeach; ?>

class <?= $command_class_name; ?> extends Command
{
    protected static $defaultName = '<?= $command_name; ?>';

    /** @var SymfonyStyle */
    protected $io;
    /** @var \Faker\Generator **/
    protected $faker;
    /** @var ProgressIndicator **/
    protected $progress;
    /** @var \Doctrine\Bundle\DoctrineBundle\Registry **/
    protected $doctrine;
    /** @var UserPasswordEncoderInterface **/
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

        //empty all tables, reset ids
        $this->truncateTables();

        //order might be important
        //change argument to load more or less of each entity
<?php foreach($class_datas as $class_data): ?>
        $this->load<?= ucfirst($class_data->getShortPluralClassName()) ?>($num = 10);
<?php endforeach; ?>

        //now loading ManyToMany data
        $this->progress->setMessage("loading many to many datas");
        $this->loadManyToManyData();

        $this->progress->finish("Done!");
        $this->io->success('Fixtures loaded!');
        return 0;
    }

<?php foreach($class_datas as $class_data): ?>
<?php include('EntityFakerFixtures.tpl.php') ?>
<?php endforeach; ?>

    protected function truncateTables()
    {
        $this->progress->setMessage("Truncating tables");

        try {
            $connection = $this->doctrine->getConnection();
            $connection->beginTransaction();
            $connection->query("SET FOREIGN_KEY_CHECKS = 0");

<?php foreach($class_datas as $class_data): ?>
            $connection->query("TRUNCATE <?= $class_data->getTableName() ?>");
<?php foreach($class_data->getPivotTableNames() as $pivot_table_name): ?>
            $connection->query("TRUNCATE <?= $pivot_table_name ?>");
<?php endforeach; ?>
<?php endforeach; ?>

            $connection->query("SET FOREIGN_KEY_CHECKS = 1");
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    protected function loadManyToManyData()
    {
<?php
$alreadyLoaded = [];
?>
<?php foreach($class_datas as $class_data): ?>
<?php
    /** @var \FakerFixtures\Doctrine\FieldData $field */
    foreach($class_data->getFields() as $field): ?>
<?php if (!empty($field->getAdder()) && $field->getType() === \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY && $field->getisOwningSide()): ?>
<?php if (!in_array($field->getAssociatedShortClassName(), $alreadyLoaded)):
    $alreadyLoaded[] = $field->getAssociatedShortClassName();
?>
        $all<?= $field->getAssociatedShortPluralClassName() ?> = $this->doctrine->getRepository(<?= $field->getAssociatedShortClassName() ?>::class)->findAll();
<?php
    endif;
    if (!in_array($class_data->getShortClassName(), $alreadyLoaded)):
    $alreadyLoaded[] = $class_data->getShortClassName();
?>
        $all<?= $class_data->getShortPluralClassName() ?> = $this->doctrine->getRepository(<?= $class_data->getShortClassName() ?>::class)->findAll();
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>

<?php foreach($class_datas as $class_data): ?>
<?php foreach($class_data->getFields() as $field): ?>
<?php if (!empty($field->getAdder()) && $field->getType() === \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY && $field->getisOwningSide()): ?>
<?php $methodName = sprintf($field->getFakerMethod(), '$all'.$field->getAssociatedShortClassName().'Entities'); ?>
<?php $var = "$" . lcfirst($class_data->getShortClassName()) ?>
        //loading data in <?= $field->getPivotTableName() ?> table
        foreach($all<?= $class_data->getShortPluralClassName() ?> as <?= "$" . lcfirst($class_data->getShortClassName()) ?>){
            $numberOf<?= $field->getFieldName() ?> = $this->faker->numberBetween($min = 1, $max = 5);
            //reset faker uniqueness
            $this->faker->unique(true)->randomElement([]);

            for($n = 0; $n < $numberOf<?= $field->getFieldName() ?>; $n++){
                <?= $var ?>-><?= $field->getAdder() ?>( $this->faker->unique()->randomElement($all<?= $field->getAssociatedShortPluralClassName() ?>) );
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
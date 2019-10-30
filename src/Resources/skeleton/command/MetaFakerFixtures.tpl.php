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
    protected $io;
    protected $output;

    public function __construct()
    {

    }

    protected function configure()
    {
        $this->setDescription('Load all fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->output = $output;

        $this->truncateTables();

        //order might be !important
        //change second argument to load more or less of each entity


        //now loading ManyToMany data

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

        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}
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

class <?= $class_name; ?> extends Command
{
    protected static $defaultName = '<?= $command_name; ?>';
    protected $io;
    protected $output;

    protected function configure()
    {
        $this->setDescription('Load all fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->output = $output;

        //order might be !important
        //change second argument to load more or less of each entity
<?php foreach($class_infos as $info): ?>
        $this->findAndExecuteCommand("<?= $sub_command_names[$info] ?>", 10);
<?php endforeach; ?>

        $this->io->success('Fixtures loaded!');
        return 0;
    }

    protected function findAndExecuteCommand($commandName, int $howMany = 10)
    {
        try {
            $command = $this->getApplication()->find("$commandName");
            $command->run(new ArrayInput(['num' => $howMany]), $this->output);
        }
        catch (\Exception $e){
            $trace = $e->getTrace();
            $help = $e->getMessage();
            if ($e instanceof \OverflowException){
                $help = "Error occurred in the command $commandName, but you likely should increase the generated number of the ASSOCIATED entity.";
            }
            $this->io->error($help);

            $parts = explode("\\", $trace[0]['file']);
            $filename = array_pop($parts);
            $this->io->warning("Have a look in {$filename} line {$trace[0]['line']}");

            die();
        }
    }
}
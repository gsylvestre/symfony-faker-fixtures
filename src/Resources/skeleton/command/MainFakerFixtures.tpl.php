<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;

class <?= $class_name; ?> extends Command
{
    protected static $defaultName = '<?= $command_name; ?>';

    protected function configure()
    {
        $this->setDescription('Load all fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $subCommands = [
<?php foreach($class_infos as $info): ?>
            '<?= $sub_command_names[$info] ?>',
<?php endforeach; ?>
        ];

        foreach($subCommands as $subCommand){
            $command = $this->getApplication()->find($subCommand);
            $command->run($input, $output);
        }

        $io->success('Fixtures loaded!');

        return 0;
    }
}
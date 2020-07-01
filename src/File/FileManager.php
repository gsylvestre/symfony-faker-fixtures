<?php

namespace FakerFixtures\File;

use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class FileManager extends \Symfony\Bundle\MakerBundle\FileManager
{

    /**
     * FileManager constructor.
     */
    public function __construct(Filesystem $fs, AutoloaderUtil $autoloaderUtil, MakerFileLinkFormatter $makerFileLinkFormatter, string $rootDirectory, string $twigDefaultPath = null)
    {
        parent::__construct($fs, $autoloaderUtil, $makerFileLinkFormatter, $rootDirectory, $twigDefaultPath);
    }


    /**
     * Delete all files in FakerFixtures dir
     * @param SymfonyStyle $io
     */
    public function deletePreviousFixture(SymfonyStyle $io): void
    {
        $filename = "FakerFixturesCommand.php";

        try {
            $finder = $this->createFinder("src/Command/");
        } catch (\Exception $e){
            if ($e instanceof DirectoryNotFoundException){
                $io->warning("Command directory does not exists! Nothing to delete.");
                return;
            }
        }

        if (!$finder->files()->name($filename)->hasResults()) {
            $io->writeln("$filename not found. Not removing anything.");
            return;
        }

        $confirmed = $io->confirm('<bg=yellow;options=bold>Are you sure you want to delete '.$filename.' ?</>', false);
        if (!$confirmed) {
            return;
        }

        $fs = new Filesystem();

        $fs->remove($finder);
        $io->text($filename . " deleted!");
    }

}
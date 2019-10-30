<?php

namespace FakerFixtures\File;

use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class FileManager extends \Symfony\Bundle\MakerBundle\FileManager
{

    /**
     * FileManager constructor.
     */
    public function __construct(Filesystem $fs, AutoloaderUtil $autoloaderUtil, string $rootDirectory, string $twigDefaultPath = null)
    {
        parent::__construct($fs, $autoloaderUtil, $rootDirectory, $twigDefaultPath);
    }


    /**
     * Delete all files in FakerFixtures dir
     * @param SymfonyStyle $io
     */
    public function deletePreviousFixtures(SymfonyStyle $io): void
    {
        try {
            $finder = $this->createFinder("src/Command/");
        } catch (\Exception $e){
            if ($e instanceof DirectoryNotFoundException){
                $io->warning("Command directory does not exists! Nothing to delete.");
                return;
            }
        }

        if (!$finder->hasResults()) {
            $io->writeln("No fixtures to remove");
            return;
        }

        $foundFiles = $finder->files();
        $foundFilesNames = [];
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
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

}
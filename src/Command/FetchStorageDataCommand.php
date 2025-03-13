<?php

namespace App\Command;

use App\Service\StorageApiService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-storage-data',
    description: 'Add a short description for your command',
)]
class FetchStorageDataCommand extends Command
{
    private StorageApiService $storageApiService;
    public function __construct(StorageApiService $storageApiService)
    {
        parent::__construct();
        $this->storageApiService = $storageApiService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching data from external API...');
        $this->storageApiService->fetchAndStoreData();
        $output->writeln('Data fetch completed.');

        return Command::SUCCESS;
    }
}

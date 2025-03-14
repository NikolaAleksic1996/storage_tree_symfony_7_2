<?php

namespace App\Command;

use App\Service\StorageApiService;
use Exception;
use Psr\Log\LoggerInterface;
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
class FetchAndStorageDataCommand extends Command
{
    private StorageApiService $storageApiService;
    private LoggerInterface $logger;
    public function __construct(StorageApiService $storageApiService, LoggerInterface $logger)
    {
        parent::__construct();
        $this->storageApiService = $storageApiService;
        $this->logger = $logger;
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
        $this->logger->info('Started fetching and storing data.');
        try {
            $this->storageApiService->fetchAndStoreData();
            $output->writeln('Data fetch and store completed successfully.');
            $this->logger->info('Data fetch and store completed successfully.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>Error occurred during the data fetch and store process: ' . $e->getMessage() . '</error>');
            $this->logger->error('Error occurred during data fetch and store: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}

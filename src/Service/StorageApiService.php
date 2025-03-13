<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\FileDirectory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StorageApiService
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function fetchAndStoreData(): void
    {
        $response = $this->client->request('GET', 'https://rest-test-eight.vercel.app/api/test#');
        $data = $response->toArray();

        $this->logger->info('Fetched data from external API', ['timestamp' => time()]);

        foreach ($data['items'] as $url) {
            $this->processUrl($url['fileUrl']);

            $this->entityManager->flush();
        }
    }

    private function processUrl(string $url): void
    {
        $parsed = parse_url($url);
//        $parsed = parse_url('http://34.8.32.234:48183/���� - ��ݷ�ʽ.lnk');
//        $parsed = parse_url('http://34.8.32.234:48183/$RECYCLE.BIN/S-1-5-21-3419125061-2900363665-2697401647-1008/desktop.ini');
        if (!isset($parsed['host'], $parsed['path'])) {
            $this->logger->warning("Invalid URL format: {$url}");
            return;
        }


        $host = $parsed['host'];
        $pathParts = explode('/', trim($parsed['path'], '/'));
        $isFile = !str_ends_with($url, '/');

        $parent = $this->getOrCreateDirectory($host, null);


        while (count($pathParts) > 1) {
            $dirName = array_shift($pathParts);
            $parent = $this->getOrCreateDirectory($dirName, $parent);
        }

        if ($isFile) {
            $fileName = array_shift($pathParts);
            $this->getOrCreateFile($fileName, $parent);
        }
    }

    private function getOrCreateDirectory(string $name, ?FileDirectory $parent): FileDirectory
    {
        $directory = $this->entityManager->getRepository(FileDirectory::class)
            ->findOneBy(['name' => $name, 'parent' => $parent]);

        if (!$directory) {
            $directory = new FileDirectory();
            $directory->setName($name);
            $directory->setParent($parent);
            $this->entityManager->persist($directory);
        }

        return $directory;
    }

    private function getOrCreateFile(string $name, ?FileDirectory $directory): void
    {
        $file = $this->entityManager->getRepository(File::class)
            ->findOneBy(['name' => $name, 'directory' => $directory]);

        if (!$file) {
            $file = new File();
            $file->setName($name);
            $file->setDirectory($directory);
            $this->entityManager->persist($file);
        }
    }
}
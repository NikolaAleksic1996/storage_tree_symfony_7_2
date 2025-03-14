<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\FileDirectory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchAndStoreData(): void
    {
        $response = $this->client->request('GET', 'https://rest-test-eight.vercel.app/api/test#');
        $data = $response->toArray();

        $this->logger->info('Data fetched from external API', ['timestamp' => date('Y-m-d H:i:s', time())]);

        $existingDirectories = $this->preloadDirectories();
        $existingFiles = $this->preloadFiles();

        $host = parse_url($data['items'][0]['fileUrl'], PHP_URL_HOST);
        $rootDirectory = $this->getOrCreateRootDirectory($host, $existingDirectories);


        foreach ($data['items'] as $url) {
            $this->processUrl($url['fileUrl'], $existingDirectories, $existingFiles, $rootDirectory);
        }
        $this->logger->info('Data persist complete', ['timestamp' => date('Y-m-d H:i:s', time())]);
        $this->entityManager->flush();
        $this->logger->info('Data import completed', ['timestamp' => date('Y-m-d H:i:s', time())]);
    }

    /**
     * @param string $url
     * @param array $existingDirectories
     * @param array $existingFiles
     * @param FileDirectory $parent
     * @return void
     */
    private function processUrl(string $url, array &$existingDirectories, array &$existingFiles, FileDirectory $parent): void
    {
        $parsed = parse_url($url);
        if (!isset($parsed['host'], $parsed['path'])) {
            $this->logger->warning("Invalid URL format: {$url}");
            return;
        }

        $pathParts = explode('/', trim($parsed['path'], '/'));
        $isFile = !str_ends_with($url, '/');

        while (count($pathParts) > 1) {
            $dirName = array_shift($pathParts);
            $parent = $this->getOrCreateDirectory($dirName, $parent, $existingDirectories);
        }

        if ($isFile) {
            $fileName = array_shift($pathParts);
            $this->getOrCreateFile($fileName, $parent, $existingFiles);
        }
    }

    /**
     * @param string $name
     * @param FileDirectory|null $parent
     * @param array $existingDirectories
     * @return FileDirectory
     */
    private function getOrCreateDirectory(string $name, ?FileDirectory $parent, array &$existingDirectories): FileDirectory
    {
        $key = $parent ? $parent->getId() . '/' . $name : $name;

        if (isset($existingDirectories[$key])) {
            return $existingDirectories[$key];
        }

        $directory = new FileDirectory();
        $directory->setName($name);
        $directory->setParent($parent);
        $this->entityManager->persist($directory);

        $existingDirectories[$key] = $directory;

        return $directory;
    }

    /**
     * @param string $name
     * @param FileDirectory|null $directory
     * @param array $existingFiles
     * @return void
     */
    private function getOrCreateFile(string $name, ?FileDirectory $directory, array &$existingFiles): void
    {
        $key = $directory ? $directory->getId() . '/' . $name : $name;

        if (isset($existingFiles[$key])) {
            return;
        }

        $file = new File();
        $file->setName($name);
        $file->setDirectory($directory);
        $this->entityManager->persist($file);

        $existingFiles[$key] = $file;
    }

    /**
     * @return array
     */
    private function preloadDirectories(): array
    {
        $directories = $this->entityManager->getRepository(FileDirectory::class)->findAll();
        $indexedDirectories = [];

        foreach ($directories as $directory) {
            $parentId = $directory->getParent() ? $directory->getParent()->getId() . '/' : '';
            $indexedDirectories[$parentId . $directory->getName()] = $directory;
        }

        return $indexedDirectories;
    }

    /**
     * @return array
     */
    private function preloadFiles(): array
    {
        $files = $this->entityManager->getRepository(File::class)->findAll();
        $indexedFiles = [];

        foreach ($files as $file) {
            $directoryId = $file->getDirectory() ? $file->getDirectory()->getId() . '/' : '';
            $indexedFiles[$directoryId . $file->getName()] = $file;
        }

        return $indexedFiles;
    }

    /**
     * @param string $host
     * @param array $existingDirectories
     * @return FileDirectory
     */
    private function getOrCreateRootDirectory(string $host, array &$existingDirectories): FileDirectory
    {
        if (isset($existingDirectories[$host])) {
            return $existingDirectories[$host];
        }

        $rootDirectory = $this->entityManager->getRepository(FileDirectory::class)
            ->findOneBy(['name' => $host, 'parent' => null]);

        if (!$rootDirectory) {
            $rootDirectory = new FileDirectory();
            $rootDirectory->setName($host);
            $rootDirectory->setParent(null);
            $this->entityManager->persist($rootDirectory);
            $this->entityManager->flush();
        }

        $existingDirectories[$host] = $rootDirectory;

        return $rootDirectory;
    }
}
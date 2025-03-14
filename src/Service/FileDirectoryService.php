<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\FileDirectory;
use App\Repository\FileDirectoryRepository;
use App\Repository\FileRepository;

class FileDirectoryService
{
    private FileDirectoryRepository $directoryRepository;
    private FileRepository $fileRepository;

    public function __construct(FileDirectoryRepository $directoryRepository, FileRepository $fileRepository)
    {
        $this->directoryRepository = $directoryRepository;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @return array
     */
    public function getStructuredData(): array
    {
        $directories = $this->directoryRepository->findAll();
        return $this->buildTree($directories);
    }

    /**
     * @param array $directories
     * @param FileDirectory|null $parent
     * @return array
     */
    private function buildTree(array $directories, ?FileDirectory $parent = null): array
    {
        $tree = [];

        foreach ($directories as $directory) {
            if ($directory->getParent() === $parent) {
                $subdirectories = $this->buildTree($directories, $directory);
                $files = $this->fileRepository->findBy(['directory' => $directory]);

                $tree[$directory->getName()] = array_merge($subdirectories, array_map(fn($file) => $file->getName(), $files));
            }
        }

        return $tree;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return FileDirectory[]
     */
    public function getDirectories(int $page = 1, int $limit = 100): array
    {
        return $this->directoryRepository->getFileDirectories(page: $page, limit: $limit);
    }

    /**
     * @param int $page
     * @param int $limit
     * @return File[]
     */
    public function getFiles(int $page = 1, int $limit = 100): array
    {
        return $this->fileRepository->getFiles(page: $page, limit: $limit);
    }
}
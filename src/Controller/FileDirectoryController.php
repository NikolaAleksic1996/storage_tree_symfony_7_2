<?php

namespace App\Controller;

use App\Service\FileDirectoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileDirectoryController extends AbstractController
{
    private FileDirectoryService $fileDirectoryService;

    public function __construct(FileDirectoryService $fileDirectoryService)
    {
        $this->fileDirectoryService = $fileDirectoryService;
    }

    #[Route('/api/files-and-directories', methods: ['GET'])]
    public function getStructuredData(): JsonResponse
    {
        return $this->json($this->fileDirectoryService->getStructuredData());
    }

    #[Route('/api/directories', methods: ['GET'])]
    public function getFileDirectories(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 100);

        return $this->json($this->fileDirectoryService->getDirectories(page: $page, limit: $limit));
    }

    #[Route('/api/files', methods: ['GET'])]
    public function getFiles(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 100);

        return $this->json($this->fileDirectoryService->getFiles(page: $page, limit: $limit));
    }
}
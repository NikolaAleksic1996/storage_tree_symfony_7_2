<?php

namespace App\Repository;

use App\Entity\FileDirectory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileDirectory>
 */
class FileDirectoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileDirectory::class);
    }

    public function getFileDirectories(int $page = 1, int $limit = 100): array
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('fd')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('fd.id', 'ASC');

        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $data = [];
        foreach ($paginator as $fileDirectory) {
            $data[] = [
                'id' => $fileDirectory->getId(),
                'name' => $fileDirectory->getName(),
            ];
        }

        return [
            'data' => $data,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
        ];
    }
}

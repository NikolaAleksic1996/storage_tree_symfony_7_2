<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getFiles(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('f')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('f.id', 'ASC');

        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $data = [];
        foreach ($paginator as $file) {
            $data[] = [
                'id' => $file->getId(),
                'name' => $file->getName(),
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

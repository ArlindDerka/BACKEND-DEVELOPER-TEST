<?php

namespace App\Repository;

use App\Entity\Auditor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Auditor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Auditor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Auditor[]    findAll()
 * @method Auditor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auditor::class);
    }
}

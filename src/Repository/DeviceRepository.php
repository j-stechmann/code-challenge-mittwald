<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\EquipmentCabinet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Device>
 */
class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    /**
     * Counts the devices currently assigned to $cabinet in the database,
     * optionally leaving out $exclude (e.g. the device being validated).
     */
    public function countByCabinet(EquipmentCabinet $cabinet, ?Device $exclude = null): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->andWhere('d.cabinet = :cabinet')
            ->setParameter('cabinet', $cabinet);

        if (null !== $exclude?->getId()) {
            $qb->andWhere('d.id != :exclude')
                ->setParameter('exclude', $exclude->getId());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

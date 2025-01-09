<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Component\Vendor\Repository;

use BitBag\OpenMarketplace\Component\Vendor\Entity\VendorInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class VendorRepository extends EntityRepository implements VendorRepositoryInterface
{
    public function findOneBySlug(string $slug): ?VendorInterface
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findAllBySettlementFrequency(string $frequency): iterable
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.settlementFrequency = :frequency')
            ->setParameter('frequency', $frequency)
            ->getQuery()
            ->getResult()
            ;
    }
}

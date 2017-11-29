<?php

namespace Pim\Bundle\AnalyticsBundle\Repository;

use Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Entity Countable Repository
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityCountableRepository extends EntityRepository implements CountableRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em, $entityName)
    {
        parent::__construct($em, $em->getClassMetadata($entityName));
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

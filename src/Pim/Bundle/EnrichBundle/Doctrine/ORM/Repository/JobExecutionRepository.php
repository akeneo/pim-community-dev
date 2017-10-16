<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * Job execution repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionRepository extends EntityRepository implements DatagridRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->addSelect('e.id')
            ->addSelect('e.status AS status')
            ->addSelect(
                "CONCAT('pim_import_export.batch_status.', e.status) as statusLabel"
            )
            ->addSelect('e.startTime as date')
            ->addSelect('j.code AS jobCode')
            ->addSelect('j.label AS jobLabel')
            ->addSelect('j.jobName as jobName')
            ->addSelect('COUNT(w.id) as warningCount')
        ;

        $qb->innerJoin('e.jobInstance', 'j');
        $qb->leftJoin('e.stepExecutions', 's');
        $qb->leftJoin('s.warnings', 'w');
        $qb->andWhere('j.type = :jobType');

        $qb->groupBy('e.id');

        return $qb;
    }

    /**
     * Get data for the last operations widget
     *
     * @param array       $types Job types to show
     * @param string|null $user
     *
     * @return array
     */
    public function getLastOperationsData(array $types, ?string $user = null)
    {
        $qb = $this->getLastOperationsQB($types, $user);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get last operations query builder
     *
     * @param array       $types
     * @param string|null $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getLastOperationsQB(array $types, ?string $user = null)
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->select('e.id, e.startTime as date, j.type, j.label, e.status, COUNT(w.id) as warningCount')
            ->innerJoin('e.jobInstance', 'j')
            ->leftJoin('e.stepExecutions', 's')
            ->leftJoin('s.warnings', 'w')
            ->groupBy('e.id')
            ->addGroupBy('date')
            ->addGroupBy('j.type')
            ->addGroupBy('j.label')
            ->addGroupBy('e.status')
            ->orderBy('e.startTime', 'DESC')
            ->setMaxResults(10);

        if (!empty($types)) {
            $qb->andWhere($qb->expr()->in('j.type', $types));
        }

        if (null !== $user) {
            $qb->andWhere($qb->expr()->eq('e.user', $qb->expr()->literal($user)));
        }

        return $qb;
    }
}

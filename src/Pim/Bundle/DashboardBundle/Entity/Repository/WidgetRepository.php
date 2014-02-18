<?php

namespace Pim\Bundle\DashboardBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;

/**
 * Repository for widgets
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WidgetRepository
{
    /** @var EntityManager */
    protected $manager;

    /**
     * Constructor
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get data for the last operations widget
     *
     * @param array $types Job types to show
     *
     * @return array
     */
    public function getLastOperationsData(array $types)
    {
        $qb = $this->manager->createQueryBuilder();

        $qb
            ->select('e.id, e.startTime as date, j.type, j.label')
            ->addSelect('CONCAT(\'pim_import_export.batch_status.\', e.status) as status')
            ->from('AkeneoBatchBundle:JobExecution', 'e')
            ->innerJoin('e.jobInstance', 'j')
            ->where(
                $qb->expr()->in('j.type', $types)
            )
            ->orderBy('e.startTime', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getArrayResult();
    }
}

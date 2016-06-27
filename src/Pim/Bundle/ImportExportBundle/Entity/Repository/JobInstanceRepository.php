<?php

namespace Pim\Bundle\ImportExportBundle\Entity\Repository;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Job instance repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6. Class will move to Pim\Bundle\ImportExportBundle\Doctrine\ORM\Repository.
 */
class JobInstanceRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface
{
    /**
     * Create datagrid query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('j');
        $qb
            ->addSelect("j.jobName as jobName")
            ->addSelect(
                "CONCAT('pim_import_export.status.', j.status) as statusLabel"
            )
            ->andWhere('j.type = :jobType');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     *
     * @return JobInstance|null
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return parent::findOneBy($criteria, $orderBy);
    }
}

<?php

namespace Pim\Bundle\ImportExportBundle\Entity\Repository;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Job instance repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\ImportExportBundle\Doctrine\ORM\Repository in 1.5
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
            ->addSelect("j.alias as jobAlias")
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
        return $this->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return array('code');
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

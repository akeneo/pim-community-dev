<?php

namespace Pim\Bundle\ImportExportBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;

/**
 * Job instance repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceRepository extends ReferableEntityRepository
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
            ->addSelect('j.alias AS jobAlias')
            ->addSelect(
                "CONCAT('pim_import_export.status.', j.status) as statusLabel"
            )
            ->andWhere('j.type = :jobType');

        return $qb;
    }
}

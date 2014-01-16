<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

/**
 * Group type repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeRepository extends ReferableEntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAll()
    {
        return $this->build()
            ->addOrderBy('group_type.code', 'ASC');
    }

    /**
     * {0inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $rootAlias = 'g';
        $qb = $this->createQueryBuilder($rootAlias);

        $labelExpr = sprintf(
            "(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)",
            $rootAlias
        );
        $qb
            ->addSelect($rootAlias)
            ->addSelect(sprintf("%s AS label", $labelExpr))
            ->addSelect('translation.label');

        $qb
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

       return $qb;
    }
}

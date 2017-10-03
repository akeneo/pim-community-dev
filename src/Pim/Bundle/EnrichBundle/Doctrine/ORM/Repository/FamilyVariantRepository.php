<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantRepository extends EntityRepository implements DatagridRepositoryInterface
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
     * @param array $parameters
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createDatagridQueryBuilder($parameters = [])
    {
        $qb = $this->createQueryBuilder('fv');
        $rootAlias = $qb->getRootAlias();

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );

        $qb
            ->select($rootAlias);

        $qb
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

        $qb->andWhere('fv.family = :family_id');

        return $qb;
    }
}

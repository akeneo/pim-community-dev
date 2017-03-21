<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Repository\ProductMassActionRepositoryInterface;

/**
 * Mass action repository for product entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionRepository implements ProductMassActionRepositoryInterface
{
    /** @var string */
    protected $entityName;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        $rootAlias = $qb->getRootAlias();
        if ($values) {
            $valueWhereCondition =
                $inset
                ? $qb->expr()->in($rootAlias, $values)
                : $qb->expr()->notIn($rootAlias, $values);
            $qb->andWhere($valueWhereCondition);
        }

        $this->buildSelect($qb, $rootAlias)
            ->resetDQLPart('from')
            ->from($this->entityName, $rootAlias);

        // Remove 'entityIds' part from querybuilder (added by product pager)
        $whereParts = $qb->getDQLPart('where')->getParts();
        $qb->resetDQLPart('where');

        foreach ($whereParts as $part) {
            if (!is_string($part) || !strpos($part, 'entityIds')) {
                $qb->andWhere($part);
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );
    }

    /**
     * Keep alias only if they are called in orderBy
     *
     * @param QueryBuilder $qb
     * @param string       $rootAlias
     *
     * @return QueryBuilder
     */
    protected function buildSelect(QueryBuilder $qb, $rootAlias)
    {
        $selects = $this->getAliasFromSelect($qb);
        $orders = $qb->getDQLPart('orderBy');

        $qb->resetDQLPart('select');
        $qb->select($rootAlias);

        foreach ($orders as $order) {
            foreach ($order->getParts() as $part) {
                $parts = explode(' ', $part);
                if (isset($parts[0]) && isset($selects[$parts[0]])) {
                    $qb->addSelect($selects[$parts[0]]);
                }
            }
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return array
     */
    protected function getAliasFromSelect(QueryBuilder $qb)
    {
        $search = ' as ';
        $data = [];
        $selects = $qb->getDQLPart('select');

        foreach ($selects as $select) {
            foreach ($select->getParts() as $part) {
                $alias = stristr($part, $search);
                if (false !== $alias) {
                    $data[str_ireplace($search, '', $alias)] = $part;
                }
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $ids)
    {
        if (empty($ids)) {
            throw new \LogicException('No products to remove');
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->delete($this->entityName, 'p')
            ->where($qb->expr()->in('p.id', $ids));

        return $qb->getQuery()->execute();
    }

    /**
     * Prepare SQL query to get common attributes
     *
     * - First subquery get all attributes (and count when they appear) added to products
     * and which are not linked to product family
     * - Second one get all attributes (and count their apparition) from product family
     * - Global query calculate total of counts
     * getting "union all" to avoid remove duplicate rows from first subquery
     *
     * @return string
     */
    protected function prepareCommonAttributesSQLQuery()
    {
        $nonFamilyAttSql = <<<SQL
    SELECT pv.attribute_id AS a_id, COUNT(DISTINCT(pv.attribute_id)) AS count_att
    FROM %product_table% p
    INNER JOIN %product_value_table% pv ON pv.entity_id = p.id
    LEFT JOIN %family_attribute_table% fa ON fa.family_id = p.family_id AND fa.attribute_id = pv.attribute_id
    WHERE p.id IN %product_ids%
    AND fa.family_id IS NULL
    GROUP BY p.id, a_id
SQL;

        $familyAttSql = <<<SQL
    SELECT fa.attribute_id AS a_id, COUNT(fa.attribute_id) AS count_att
    FROM %product_table% p
    INNER JOIN %family_table% f ON f.id = p.family_id
    INNER JOIN %family_attribute_table% fa ON fa.family_id = f.id
    WHERE p.id IN %product_ids%
    GROUP BY a_id
SQL;

        $commonAttSql = <<<SQL
    SELECT SUM(a.count_att) AS count_att, a.a_id
    FROM (%non_family_att_sql% UNION ALL %family_att_sql%) a
    GROUP BY a.a_id
    HAVING count_att = %product_ids_count%
SQL;

        return strtr(
            $commonAttSql,
            [
                '%non_family_att_sql%' => $nonFamilyAttSql,
                '%family_att_sql%'     => $familyAttSql
            ]
        );
    }
}

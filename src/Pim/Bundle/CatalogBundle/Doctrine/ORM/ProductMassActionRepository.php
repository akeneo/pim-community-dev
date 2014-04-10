<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;

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
     * @param EntityManager    $em
     * @param string           $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em = $em;
        $this->entityName       = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, $values)
    {
        $rootAlias = $qb->getRootAlias();
        if ($values) {
            $valueWhereCondition =
                $inset
                ? $qb->expr()->in($rootAlias, $values)
                : $qb->expr()->notIn($rootAlias, $values);
            $qb->andWhere($valueWhereCondition);
        }

        $qb
            ->resetDQLPart('select')
            ->resetDQLPart('from')
            ->select($rootAlias)
            ->from($this->entityName, $rootAlias);

        // Remove 'entityIds' part from querybuilder (added by flexible pager)
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
     * {@inheritdoc}
     */
    public function findCommonAttributeIds(array $productIds)
    {
        // Prepare SQL query
        $commonAttSql = $this->prepareCommonAttributesSQLQuery();
        $commonAttSql = strtr(
            $commonAttSql,
            [
                '%product_ids%' => '('. implode($productIds, ',') .')',
                '%product_ids_count%'  => count($productIds)
            ]
        );
        $commonAttSql = $this->prepareDBALQuery($commonAttSql);

        // Execute SQL query
        $stmt = $this->getEntityManager()->getConnection()->prepare($commonAttSql);
        $stmt->execute();

        $attributes = $stmt->fetchAll();
        $attributeIds = array();
        foreach ($attributes as $attributeId) {
            $attributeIds[] = (int) $attributeId['a_id'];
        }

        return $attributeIds;
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
                '%family_att_sql%' => $familyAttSql
            ]
        );
    }
}

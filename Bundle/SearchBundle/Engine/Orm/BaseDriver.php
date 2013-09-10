<?php

namespace Oro\Bundle\SearchBundle\Engine\Orm;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Engine\Indexer;

abstract class BaseDriver
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManager         $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function initRepo(EntityManager $em, ClassMetadata $class)
    {
        $this->entityName = $class->name;
        $this->em = $em;
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string $alias
     *
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($alias)
    {
        return $this->em->createQueryBuilder()
            ->select($alias)
            ->from($this->entityName, $alias);
    }

    /**
     * Search query by Query builder object
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return array
     */
    public function search(Query $query)
    {
        $qb = $this->getRequestQB($query);
        $qb->distinct(true);

        // set max results count
        if ($query->getMaxResults() > 0) {
            $qb->setMaxResults($query->getMaxResults());
        }

        // set first result offset
        if ($query->getFirstResult() > 0) {
            $qb->setFirstResult($query->getFirstResult());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get count of records without limit parameters in query
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return integer
     */
    public function getRecordsCount(Query $query)
    {
        $qb = $this->getRequestQB($query, false);
        $qb->select($qb->expr()->countDistinct('search.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Add text search to qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param array                      $searchCondition
     * @param boolean                    $setOrderBy
     *
     * @return string
     */
    protected function addTextField(QueryBuilder $qb, $index, $searchCondition, $setOrderBy = true)
    {
        $useFieldName = $searchCondition['fieldName'] == '*' ? false : true;

        // TODO Need to clarify search requirements in scope of CRM-214
        if ($searchCondition['condition'] == Query::OPERATOR_CONTAINS) {
            $searchString = $this->createContainsStringQuery($index, $useFieldName);
        } else {
            $searchString = $this->createNotContainsStringQuery($index, $useFieldName);
        }
        $whereExpr = $searchCondition['type'] . ' (' . $searchString . ')';

        $this->setFieldValueStringParameter($qb, $index, $searchCondition['fieldValue'], $searchCondition['condition']);

        if ($useFieldName) {
            $qb->setParameter('field' . $index, $searchCondition['fieldName']);
        }

        if ($setOrderBy) {
            $this->setTextOrderBy($qb, $index);
        }

        return $whereExpr;
    }

    /**
     * Create search string for string parameters (contains)
     *
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createContainsStringQuery($index, $useFieldName = true)
    {
        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = 'textField.field = :field' . $index . ' AND ';
        }

        return $stringQuery . 'textField.value LIKE :value' . $index;
    }

    /**
     * Create search string for string parameters (not contains)
     *
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createNotContainsStringQuery($index, $useFieldName = true)
    {
        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = 'textField.field = :field' . $index . ' AND ';
        }

        return $stringQuery . 'textField.value NOT LIKE :value' . $index;
    }

    /**
     * Set string parameter for qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param string                     $fieldValue
     * @param string                     $searchCondition
     */
    protected function setFieldValueStringParameter(QueryBuilder $qb, $index, $fieldValue, $searchCondition)
    {
        $qb->setParameter('value' . $index, '%' . str_replace(' ', '%', $fieldValue) . '%');
    }

    /**
     * Add non string search to qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param array                      $searchCondition
     *
     * @return string
     */
    protected function addNonTextField(QueryBuilder $qb, $index, $searchCondition)
    {
        $joinAlias = $searchCondition['fieldType'] . 'Field';
        $qb->setParameter('field' . $index, $searchCondition['fieldName']);
        $qb->setParameter('value' . $index, $searchCondition['fieldValue']);

        return $searchCondition['type'] . ' (' . $this->createNonTextQuery(
            $joinAlias,
            $index,
            $searchCondition['condition']
        ) . ')';
    }

    /**
     * Create search string for non string parameters
     *
     * @param $joinAlias
     * @param $index
     * @param $condition
     *
     * @return string
     */
    protected function createNonTextQuery($joinAlias, $index, $condition)
    {
        if ($condition == Query::OPERATOR_IN) {
            $searchString
                = $joinAlias . '.field= :field' . $index . ' AND ' . $joinAlias . '.value ' . $condition . ' (:value'
                . $index . ')';
        } elseif ($condition == Query::OPERATOR_NOT_IN) {
            $searchString
                =
                $joinAlias . '.field= :field' . $index . ' AND ' . $joinAlias . '.value NOT IN (:value' . $index . ')';
        } else {
            $searchString
                = $joinAlias . '.field= :field' . $index . ' AND ' . $joinAlias . '.value ' . $condition . ' :value'
                . $index;
        }

        return $searchString;
    }

    /**
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     * @param boolean                              $setOrderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getRequestQB(Query $query, $setOrderBy = true)
    {
        $qb = $this->createQueryBuilder('search')
            ->select(array('search as item', 'text'))
            ->leftJoin('search.textFields', 'text', 'WITH', 'text.field = :allTextField')
            ->leftJoin('search.textFields', 'textField')
            ->leftJoin('search.integerFields', 'integerField')
            ->leftJoin('search.decimalFields', 'decimalField')
            ->leftJoin('search.datetimeFields', 'datetimeField')
            ->setParameter('allTextField', Indexer::TEXT_ALL_DATA_FIELD);

        $this->setFrom($query, $qb);

        $whereExpr = array();
        if (count($query->getOptions())) {
            foreach ($query->getOptions() as $index => $searchCondition) {
                if ($searchCondition['fieldType'] == Query::TYPE_TEXT) {
                    $whereExpr[] = $this->addTextField($qb, $index, $searchCondition, $setOrderBy);
                } else {
                    $whereExpr[] = $this->addNonTextField($qb, $index, $searchCondition);
                }
            }
            if (substr($whereExpr[0], 0, 3) == 'and') {
                $whereExpr[0] = substr($whereExpr[0], 3, strlen($whereExpr[0]));
            }

            $qb->andWhere(implode(' ', $whereExpr));
        }

        if ($setOrderBy) {
            $this->addOrderBy($query, $qb);
        }

        return $qb;
    }

    /**
     * Set from parameters from search query
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     * @param \Doctrine\ORM\QueryBuilder           $qb
     */
    protected function setFrom(Query $query, QueryBuilder $qb)
    {
        $useFrom = true;
        foreach ($query->getFrom() as $from) {
            if ($from == '*') {
                $useFrom = false;
            }
        }
        if ($useFrom) {
            $qb->andWhere($qb->expr()->in('search.alias', $query->getFrom()));
        }
    }

    /**
     * Set order by for search query
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     * @param \Doctrine\ORM\QueryBuilder           $qb
     */
    protected function addOrderBy(Query $query, QueryBuilder $qb)
    {
        $orderBy = $query->getOrderBy();

        if ($orderBy) {
            $orderRelation = $query->getOrderType() . 'Fields';
            $qb->leftJoin('search.' . $orderRelation, 'orderTable', 'WITH', 'orderTable.field = :orderField')
                ->orderBy('orderTable.value', $query->getOrderDirection())
                ->setParameter('orderField', $orderBy);
        }
    }

    /**
     * Set fulltext range order by
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param int                        $index
     */
    protected function setTextOrderBy(QueryBuilder $qb, $index)
    {
    }
}

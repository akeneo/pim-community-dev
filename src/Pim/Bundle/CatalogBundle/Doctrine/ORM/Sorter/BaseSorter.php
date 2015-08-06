<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Sorter\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface;

/**
 * Base sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSorter implements AttributeSorterInterface, FieldSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate a sorter
     *
     * @param array $supportedAttributes
     * @param array $supportedFields
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedFields = []
    ) {
        $this->supportedAttributes = array_merge(
            [
                AttributeTypes::IDENTIFIER,
                AttributeTypes::TEXT,
                AttributeTypes::TEXTAREA,
                AttributeTypes::NUMBER,
                AttributeTypes::BOOLEAN,
                AttributeTypes::DATE
            ],
            $supportedAttributes
        );
        $this->supportedFields = array_merge(
            ['enabled', 'created', 'updated'],
            $supportedFields
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array(
            $field,
            $this->supportedFields
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            $this->supportedAttributes
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $scope = null)
    {
        $aliasPrefix = 'sorter';
        $joinAlias   = $aliasPrefix.'V'.$attribute->getCode();
        $backendType = $attribute->getBackendType();

        // join to value and sort on
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        // Remove current join in order to put the orderBy related join
        // at first place in the join queue for performances reasons
        $joinsSet = $this->qb->getDQLPart('join');
        $this->qb->resetDQLPart('join');

        $this->qb->leftJoin(
            $this->qb->getRootAlias().'.values',
            $joinAlias,
            'WITH',
            $condition
        );
        $this->qb->addOrderBy($joinAlias.'.'.$backendType, $direction);

        $idField = $this->qb->getRootAlias().'.id';
        $this->qb->addOrderBy($idField);

        // Reapply previous join after the orderBy related join
        // TODO : move this part in re-usable class
        $this->applyJoins($joinsSet);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;
        $this->qb->addOrderBy($field, $direction);

        $idField = $this->qb->getRootAlias().'.id';
        $this->qb->addOrderBy($idField);

        return $this;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AttributeInterface $attribute the attribute
     * @param string             $joinAlias the value join alias
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     *
     * @throws \Pim\Bundle\CatalogBundle\Exception\ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(
        AttributeInterface $attribute,
        $joinAlias,
        $locale = null,
        $scope = null
    ) {
        $joinHelper = new ValueJoin($this->qb);

        return $joinHelper->prepareCondition($attribute, $joinAlias, $locale, $scope);
    }

    /**
     * Reapply joins from a set of joins got from getDQLPart('join')
     *
     * @param array $joinsSet
     */
    protected function applyJoins($joinsSet)
    {
        foreach ($joinsSet as $joins) {
            foreach ($joins as $join) {
                if ($join->getJoinType() === Join::LEFT_JOIN) {
                    $this->qb->leftJoin(
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        $join->getCondition(),
                        $join->getIndexBy()
                    );
                } else {
                    $this->qb->join(
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        $join->getCondition(),
                        $join->getIndexBy()
                    );
                }
            }
        }
    }
}

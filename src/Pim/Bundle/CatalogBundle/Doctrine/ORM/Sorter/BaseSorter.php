<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

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

    /** @var CatalogContext */
    protected $context;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate a sorter
     *
     * @param CatalogContext $context
     * @param array          $extraSupportedAttributes
     * @param array          $extraSupportedFields
     */
    public function __construct(
        CatalogContext $context,
        array $extraSupportedAttributes = [],
        array $extraSupportedFields = []
    ) {
        $this->context = $context;
        $this->supportedAttributes = array_merge(
            [
                'pim_catalog_identifier',
                'pim_catalog_text',
                'pim_catalog_textarea',
                'pim_catalog_number',
                'pim_catalog_boolean',
                'pim_catalog_date'
            ],
            $extraSupportedAttributes
        );
        $this->supportedFields = array_merge(
            ['enabled', 'created', 'updated'],
            $extraSupportedFields
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
    public function addAttributeSorter(AttributeInterface $attribute, $direction)
    {
        $aliasPrefix = 'sorter';
        $joinAlias   = $aliasPrefix.'V'.$attribute->getCode();
        $backendType = $attribute->getBackendType();

        // join to value and sort on
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
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
    public function addFieldSorter($field, $direction)
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
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AttributeInterface $attribute, $joinAlias)
    {
        $joinHelper = new ValueJoin($this->qb, $this->context);

        return $joinHelper->prepareCondition($attribute, $joinAlias);
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

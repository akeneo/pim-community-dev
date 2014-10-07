<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Entity sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntitySorter implements AttributeSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * Instanciate a sorter
     *
     * @param CatalogContext $context
     */
    public function __construct(CatalogContext $context)
    {
        $this->context = $context;
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
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            [
                'pim_catalog_multiselect', // TODO : to disable, not make sense on a many relation
                'pim_catalog_simpleselect'
            ]
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

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $this->qb->leftJoin(
            $this->qb->getRootAlias().'.values',
            $joinAlias,
            'WITH',
            $condition
        );

        // then to option and option value to sort on
        $joinAliasOpt = $aliasPrefix.'O'.$attribute->getCode();
        $condition    = $joinAliasOpt.".attribute = ".$attribute->getId();
        $this->qb->leftJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);

        $joinAliasOptVal = $aliasPrefix.'OV'.$attribute->getCode();
        $condition       = $joinAliasOptVal.'.locale = '.$this->qb->expr()->literal($this->context->getLocaleCode());
        $this->qb->leftJoin($joinAliasOpt.'.optionValues', $joinAliasOptVal, 'WITH', $condition);

        $this->qb->addOrderBy($joinAliasOpt.'.code', $direction);
        $this->qb->addOrderBy($joinAliasOptVal.'.value', $direction);

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
}

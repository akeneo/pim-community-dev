<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

/**
 * Reference data sorter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSorter implements AttributeSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /**
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(ConfigurationRegistryInterface $registry)
    {
        $this->registry = $registry;
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
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $scope = null)
    {
        if (null === $locale) {
            throw new \InvalidArgumentException(
                sprintf('Cannot prepare condition on type "%s" without locale', $attribute->getType())
            );
        }

        $aliasPrefix = 'sorter';

        // join to values
        $joinAlias = $aliasPrefix . 'V' . $attribute->getCode();
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $this->qb->leftJoin(
            current($this->qb->getRootAliases()) . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        // join to reference data
        $referenceDataName = $attribute->getReferenceDataName();
        $joinAliasOpt = $this->getUniqueAlias('reference_data' . $referenceDataName);
        $this->qb->leftJoin($joinAlias . '.' . $referenceDataName, $joinAliasOpt);

        $this->qb->addOrderBy($joinAliasOpt . '.code', $direction);
        $idField = current($this->qb->getRootAliases()) . '.id';
        $this->qb->addOrderBy($idField);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        return '' !== $referenceDataName &&
            null !== $referenceDataName &&
            null !== $this->registry->get($referenceDataName);
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AttributeInterface $attribute the attribute
     * @param string             $joinAlias the value join alias
     * @param string             $locale    the locale
     * @param string             $scope     the scope
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
     * Get a unique alias
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getUniqueAlias($alias)
    {
        return uniqid($alias);
    }
}

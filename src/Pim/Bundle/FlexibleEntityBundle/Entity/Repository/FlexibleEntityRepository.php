<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleEntityRepositoryInterface;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\LocalizableInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;

/**
 * Base repository for flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleEntityRepository extends EntityRepository implements
    LocalizableInterface,
    ScopableInterface,
    FlexibleEntityRepositoryInterface
{
    /**
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * @param FlexibleQueryBuilder
     */
    protected $flexibleQB;

    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
    }

    /**
     * Set flexible entity config
     *
     * @param array $config
     *
     * @return FlexibleEntityRepository
     */
    public function setFlexibleConfig($config)
    {
        $this->flexibleConfig = $config;

        return $this;
    }

    /**
     * Return asked locale code or default one
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setLocale($code)
    {
        $this->locale = $code;

        return $this;
    }

    /**
     * Return asked scope code or default one
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set scope code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }

    /**
     * Set flexible query builder
     *
     * @param FlexibleQueryBuilder $flexibleQB
     *
     * @return FlexibleEntityRepository
     */
    public function setFlexibleQueryBuilder($flexibleQB)
    {
        $this->flexibleQB = $flexibleQB;

        return $this;
    }

    /**
     * Finds entities and attributes values by a set of criteria, same coverage than findBy
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    public function findAllByAttributes(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this
            ->findAllByAttributesQB($attributes, $criteria, $orderBy, $limit, $offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Apply a filter by attribute value
     *
     * @param QueryBuilder $qb             query builder to update
     * @param string       $attributeCode  attribute code
     * @param string|array $attributeValue value(s) used to filter
     * @param string       $operator       operator to use
     */
    public function applyFilterByAttribute(QueryBuilder $qb, $attributeCode, $attributeValue, $operator = '=')
    {
        $attributeName = $this->flexibleConfig['attribute_class'];
        $attributeRepo = $this->_em->getRepository($attributeName);
        $attribute = $attributeRepo->findOneByEntityAndCode($this->_entityName, $attributeCode);

        if ($attribute) {
            $this->getFlexibleQueryBuilder($qb)->addAttributeFilter($attribute, $operator, $attributeValue);

        } else {
            $field = current($qb->getRootAliases()).'.'.$attributeCode;
            $qb->andWhere(
                $this->getFlexibleQueryBuilder($qb)->prepareCriteriaCondition($field, $operator, $attributeValue)
            );
        }
    }

    /**
     * Apply a sort by attribute value
     *
     * @param QueryBuilder $qb            query builder to update
     * @param string       $attributeCode attribute code
     * @param string       $direction     direction to use
     */
    public function applySorterByAttribute(QueryBuilder $qb, $attributeCode, $direction)
    {
        $attributeName = $this->flexibleConfig['attribute_class'];
        $attributeRepo = $this->_em->getRepository($attributeName);
        $attribute = $attributeRepo->findOneByEntityAndCode($this->_entityName, $attributeCode);

        if ($attribute) {
            $this->getFlexibleQueryBuilder($qb)->addAttributeSorter($attribute, $direction);
        } else {
            $qb->addOrderBy(current($qb->getRootAliases()).'.'.$attributeCode, $direction);
        }
    }

    /**
     * Load a flexible entity with its attribute values
     *
     * @param integer $id
     *
     * @return AbstractFlexible|null
     * @throws NonUniqueResultException
     */
    public function findOneByWithValues($id)
    {
        $qb = $this->findAllByAttributesQB(array(), array('id' => $id));
        $qb->leftJoin('Attribute.translations', 'AttributeTranslations');
        $qb->leftJoin('Attribute.availableLocales', 'AttributeLocales');
        $qb->addSelect('Value');
        $qb->addSelect('Attribute');
        $qb->addSelect('AttributeTranslations');
        $qb->addSelect('AttributeLocales');
        $qb->leftJoin('Attribute.group', 'AttributeGroup');
        $qb->addSelect('AttributeGroup');
        $qb->leftJoin('AttributeGroup.translations', 'AGroupTranslations');
        $qb->addSelect('AGroupTranslations');

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return FlexibleQueryBuilder
     */
    protected function getFlexibleQueryBuilder($qb)
    {
        if (!$this->flexibleQB) {
            throw new \LogicException('Flexible query builder must be configured');
        }

        $this->flexibleQB
            ->setQueryBuilder($qb)
            ->setLocale($this->getLocale())
            ->setScope($this->getScope());

        return $this->flexibleQB;
    }

    /**
     * Add join to values tables
     *
     * @param QueryBuilder $qb
     */
    protected function addJoinToValueTables(QueryBuilder $qb)
    {
        $qb->leftJoin(current($qb->getRootAliases()).'.values', 'Value')
            ->leftJoin('Value.attribute', 'Attribute')
            ->leftJoin('Value.options', 'ValueOption')
            ->leftJoin('ValueOption.optionValues', 'AttributeOptionValue');
    }

    /**
     * Finds entities and attributes values by a set of criteria, same coverage than findBy
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    protected function findAllByAttributesQB(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $qb = $this->createQueryBuilder('Entity');
        $this->addJoinToValueTables($qb);

        if (!is_null($criteria)) {
            foreach ($criteria as $attCode => $attValue) {
                $this->applyFilterByAttribute($qb, $attCode, $attValue);
            }
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $attCode => $direction) {
                $this->applySorterByAttribute($qb, $attCode, $direction);
            }
        }

        // use doctrine paginator to avoid count problem with left join of values
        if (!is_null($offset) and !is_null($limit)) {
            $qb->setFirstResult($offset)->setMaxResults($limit);
            $paginator = new Paginator($qb->getQuery());

            return $paginator;
        }

        return $qb;
    }
}

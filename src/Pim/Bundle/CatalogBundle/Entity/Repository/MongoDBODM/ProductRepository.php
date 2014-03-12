<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Product repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository implements ProductRepositoryInterface,
 ReferableEntityRepositoryInterface
{
    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

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
     * {@inheritdoc}
     */
    public function findAllByAttributes(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildByScope($scope)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildByChannelAndCompleteness(Channel $channel)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExistingFamily()
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array())
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        return;
    }

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
        if (!$this->locale) {
            $this->locale = $this->flexibleConfig['default_locale'];
        }

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
        if (!$this->scope) {
            $this->scope = $this->flexibleConfig['default_scope'];
        }

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
     * {@inheritdoc}
     */
    public function findOneByWithValues($id)
    {
        // FIXME_MONGO Shortcut, but must do the same thing
        // than the ORM one
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $attributeId = $value->getAttribute()->getId();
        $attributeBackend = $value->getAttribute()->getBackendType();
        $data = $value->getData();

        $result = $this->createQueryBuilder()
            ->hydrate(false)
            ->field("values.".$attributeBackend)->equals($data)
            ->field("values.attributeId")->equals($attributeId)
            ->getQuery()
            ->getSingleResult();

        $foundValueId = null;

        if ((1 === count($result)) && isset($result['_id'])) {
            $foundValueId = $result['_id']->id;
        }

        return (
            (0 !== count($result)) &&
            ($value->getId() === $foundValueId)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function countProductsPerChannels()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function countCompleteProductsPerChannels()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function setFlexibleQueryBuilder($flexibleQB)
    {
        $this->flexibleQB = $flexibleQB;

        return $this;

    }

    /**
     * {@inheritdoc}
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
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder();

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByAttribute($qb, Attribute $attribute, $value, $operator = '=')
    {
        $this->getFlexibleQueryBuilder($qb)->addAttributeFilter($attribute, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByField($qb, $field, $value, $operator = '=')
    {
        $this->getFlexibleQueryBuilder($qb)->addFieldFilter($field, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByAttribute($qb, Attribute $attribute, $direction)
    {
        $this->getFlexibleQueryBuilder($qb)->addAttributeSorter($attribute, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByField($qb, $field, $direction)
    {
        $this->getFlexibleQueryBuilder($qb)->addFieldSorter($field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, $productIds, $include)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }
}

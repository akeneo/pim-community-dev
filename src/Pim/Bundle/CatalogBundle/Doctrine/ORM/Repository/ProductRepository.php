<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements
    ProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    CursorableRepositoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $queryBuilderFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ConfigurationRegistryInterface */
    protected $referenceDataRegistry;

    /**
     * {@inheritdoc}
     */
    public function setProductQueryBuilderFactory(ProductQueryBuilderFactoryInterface $factory)
    {
        $this->queryBuilderFactory = $factory;
    }

    /**
     * Set attribute repository
     *
     * @param AttributeRepositoryInterface $attributeRepository
     *
     * @return ProductRepository
     */
    public function setAttributeRepository(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;

        return $this;
    }

    /**
     * Set reference data registry
     *
     * @param ConfigurationRegistryInterface $registry
     *
     * @return ProductRepositoryInterface
     */
    public function setReferenceDataRegistry(ConfigurationRegistryInterface $registry = null)
    {
        $this->referenceDataRegistry = $registry;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since 1.3, we keep this public method for connector compatibility, this visibility may change
     *
     * @return QueryBuilder
     */
    public function buildByScope($scope)
    {
        $productQb = $this->queryBuilderFactory->create();
        $qb = $productQb->getQueryBuilder();
        $this->addJoinToValueTables($qb);
        $rootAlias = current($qb->getRootAliases());
        $qb
            ->andWhere(
                $qb->expr()->eq($rootAlias.'.enabled', ':enabled')
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('Value.scope', ':scope'),
                    $qb->expr()->isNull('Value.scope')
                )
            )
            ->setParameter('enabled', true)
            ->setParameter('scope', $scope);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function buildByChannelAndCompleteness(ChannelInterface $channel)
    {
        $scope = $channel->getCode();
        $qb = $this->buildByScope($scope);
        $rootAlias = current($qb->getRootAliases());
        $expression =
            'pCompleteness.product = '.$rootAlias.' AND '.
            $qb->expr()->eq('pCompleteness.ratio', '100').' AND '.
            $qb->expr()->eq('pCompleteness.channel', $channel->getId());

        $rootEntity          = current($qb->getRootEntities());
        $completenessMapping = $this->_em->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');
        $completenessClass   = $completenessMapping['targetEntity'];
        $qb->innerJoin(
            $completenessClass,
            'pCompleteness',
            'WITH',
            $expression
        );

        $treeId = $channel->getCategory()->getId();
        $expression = $qb->expr()->eq('pCategory.root', $treeId);
        $qb->innerJoin(
            $rootAlias.'.categories',
            'pCategory',
            'WITH',
            $expression
        );

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('Product');
        $this->addJoinToValueTables($qb);
        $rootAlias = current($qb->getRootAliases());
        $qb->andWhere(
            $qb->expr()->in($rootAlias.'.id', $ids)
        );

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(GroupInterface $variantGroup, array $criteria = array())
    {
        $qb = $this->createQueryBuilder('Product');

        $qb
            ->where(':variantGroup MEMBER OF Product.groups')
            ->setParameter('variantGroup', $variantGroup);

        $index = 0;
        foreach ($criteria as $item) {
            $code = $item['attribute']->getCode();
            ++$index;
            $qb
                ->innerJoin(
                    'Product.values',
                    sprintf('Value_%s', $code),
                    'WITH',
                    sprintf('Value_%s.attribute = ?%d', $code, $index)
                )
                ->setParameter($index, $item['attribute']);

            if (isset($item['option'])) {
                ++$index;
                $qb->andWhere(sprintf('Value_%s.option = ?%d', $code, $index))
                    ->setParameter($index, $item['option']);
            } elseif (isset($item['referenceData'])) {
                ++$index;
                $qb->andWhere(sprintf('Value_%s.%s = ?%d', $code, $item['referenceData']['name'], $index))
                    ->setParameter($index, $item['referenceData']['data']);
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithAttribute(AttributeInterface $attribute)
    {
        return $this
            ->createQueryBuilder('p')
            ->leftJoin('p.values', 'value')
            ->leftJoin('value.attribute', 'attribute')
            ->where('attribute=:attribute')
            ->setParameter('attribute', $attribute)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithAttributeOption(AttributeOptionInterface $option)
    {
        $backendType = $option->getAttribute()->getBackendType();

        $qb = $this
            ->createQueryBuilder('p')
            ->leftJoin('p.values', 'value')
            ->leftJoin(sprintf('value.%s', $backendType), 'option');

        if ('options' === $backendType) {
            $qb->where(
                $qb->expr()->in('option', ':option')
            );
        } else {
            $qb->where('option=:option');
        }

        return $qb
            ->setParameter('option', $option)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        $qb = $this->getFullProductQB();

        return $qb
            ->where('p.id=:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Will be removed in 1.5
     */
    public function getFullProducts(array $productIds, array $attributeIds = [])
    {
        $qb = $this->getFullProductQB();
        $qb
            ->addSelect('c, assoc, g')
            ->leftJoin('v.attribute', 'a')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('p.associations', 'assoc')
            ->leftJoin('p.groups', 'g')
            ->where($qb->expr()->in('p.id', $productIds));

        if (!empty($attributeIds)) {
            $qb->andWhere($qb->expr()->in('a.id', $attributeIds));
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get full product query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getFullProductQB()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p, f, v, pr, m, o, os')
            ->leftJoin('p.family', 'f')
            ->leftJoin('p.values', 'v')
            ->leftJoin('v.prices', 'pr')
            ->leftJoin('v.media', 'm')
            ->leftJoin('v.option', 'o')
            ->leftJoin('v.options', 'os');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return array($this->attributeRepository->getIdentifierCode());
    }

    /**
     * Returns the ProductValue class
     *
     * @return string
     */
    protected function getValuesClass()
    {
        return $this->getClassMetadata()
            ->getAssociationTargetClass('values');
    }

    /**
     * Returns the Attribute class
     *
     * @return string
     */
    protected function getAttributeClass()
    {
        return $this->getEntityManager()
            ->getClassMetadata($this->getValuesClass())
            ->getAssociationTargetClass('attribute');
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createGroupDatagridQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $isCheckedExpr =
            'CASE WHEN ' .
            '(:currentGroup MEMBER OF p.groups '.
            'OR p.id IN (:data_in)) AND p.id NOT IN (:data_not_in) '.
            'THEN true ELSE false END';
        $qb
            ->addSelect($isCheckedExpr.' AS is_checked');

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createVariantGroupDatagridQueryBuilder()
    {
        $qb = $this->createGroupDatagridQueryBuilder();
        $qb->andWhere($qb->expr()->in('p.id', ':productIds'));

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createAssociationDatagridQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $qb
            ->leftJoin(
                'Pim\Bundle\CatalogBundle\Model\Association',
                'pa',
                'WITH',
                'pa.associationType = :associationType AND pa.owner = :product AND p MEMBER OF pa.products'
            );

        $qb->andWhere($qb->expr()->neq('p', ':product'));

        $isCheckedExpr =
            'CASE WHEN (pa.id IS NOT NULL OR p.id IN (:data_in)) AND p.id NOT IN (:data_not_in) ' .
            'THEN true ELSE false END';

        $isAssociatedExpr = 'CASE WHEN pa.id IS NOT NULL THEN true ELSE false END';

        $qb
            ->addSelect($isCheckedExpr.' AS is_checked')
            ->addSelect($isAssociatedExpr.' AS is_associated');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $criteria = array(
            'attribute'                              => $value->getAttribute(),
            $value->getAttribute()->getBackendType() => $value->getData()
        );
        $result = $this->getEntityManager()->getRepository(get_class($value))->findBy($criteria);

        return (
            (0 !== count($result)) &&
            !(1 === count($result) && $value === ($result instanceof \Iterator ? $result->current() : current($result)))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEligibleProductIdsForVariantGroup($variantGroupId)
    {
        $sql = 'SELECT v.entity_id AS product_id, gp.group_id, ga.group_id ' .
            'FROM pim_catalog_group g ' .
            'INNER JOIN pim_catalog_group_attribute ga ON ga.group_id = g.id ' .
            'INNER JOIN %product_value_table% v ON v.attribute_id = ga.attribute_id ' .
            'LEFT JOIN pim_catalog_group_product gp ON (v.entity_id = gp.product_id) ' .
            'INNER JOIN pim_catalog_group_type gt on gt.id = g.type_id ' .
            'WHERE ga.group_id = :groupId AND gt.code = "VARIANT" ' .
            'AND (v.option_id IS NOT NULL';

        if (null !== $this->referenceDataRegistry) {
            $references = $this->referenceDataRegistry->all();
            if (!empty($references)) {
                $valueMetadata = QueryBuilderUtility::getProductValueMetadata($this->_em, $this->_entityName);

                foreach ($references as $code => $referenceData) {
                    if (ConfigurationInterface::TYPE_SIMPLE === $referenceData->getType()) {
                        if ($valueMetadata->isAssociationWithSingleJoinColumn($code)) {
                            $sql .= sprintf(
                                ' OR v.%s IS NOT NULL',
                                $valueMetadata->getSingleAssociationJoinColumnName($code)
                            );
                        }
                    }
                }
            }
        }

        $sql .= ') ' .
            'GROUP BY v.entity_id ' .
            'HAVING (gp.group_id IS NULL OR gp.group_id = ga.group_id) ' .
            'AND COUNT(ga.attribute_id) = (SELECT COUNT(*) FROM pim_catalog_group_attribute WHERE group_id = :groupId)';

        $sql = QueryBuilderUtility::prepareDBALQuery($this->_em, $this->_entityName, $sql);
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $productIds = array_map(
            function ($row) {
                return $row['product_id'];
            },
            $results
        );

        return $productIds;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $pqb = $this->queryBuilderFactory->create();
        $qb = $pqb->getQueryBuilder();
        $attribute = $this->getIdentifierAttribute();
        $pqb->addFilter($attribute->getCode(), Operators::EQUALS, $identifier);
        $result = $qb->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        return reset($result);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $pqb = $this->queryBuilderFactory->create();
        $qb = $pqb->getQueryBuilder();
        $result = $qb->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneById($id)
    {
        $pqb = $this->queryBuilderFactory->create();
        $pqb->addFilter('id', '=', $id);
        $qb = $pqb->getQueryBuilder();
        $result = $qb->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        return reset($result);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByWithValues($id)
    {
        $productQb = $this->queryBuilderFactory->create();
        $qb = $productQb->getQueryBuilder();
        $rootAlias = current($qb->getRootAliases());
        $this->addJoinToValueTables($qb);
        $qb->leftJoin('Attribute.availableLocales', 'AttributeLocales');
        $qb->addSelect('Value');
        $qb->addSelect('Attribute');
        $qb->addSelect('AttributeLocales');
        $qb->leftJoin('Attribute.group', 'AttributeGroup');
        $qb->addSelect('AttributeGroup');
        $qb->andWhere(
            $qb->expr()->eq($rootAlias.'.id', $id)
        );

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
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
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('a.id')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->where($qb->expr()->in('p.id', $productIds))
            ->groupBy('a.id');

        $attributes = $qb->getQuery()->getArrayResult();
        $attributeIds = array();
        foreach ($attributes as $attribute) {
            $attributeIds[] = $attribute['id'];
        }

        return $attributeIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectManager()
    {
        return $this->getEntityManager();
    }

    /**
     * Return the identifier attribute
     *
     * @return AttributeInterface|null
     */
    protected function getIdentifierAttribute()
    {
        return $this->attributeRepository->findOneBy(['attributeType' => AttributeTypes::IDENTIFIER]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsByGroup(GroupInterface $group, $maxResults)
    {
        $products = $this
            ->createQueryBuilder('p')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->setMaxResults($maxResults)
            ->execute();

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByGroup(GroupInterface $group)
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }
}

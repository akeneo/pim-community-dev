<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
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

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

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
     * Set group repository
     *
     * @param GroupRepositoryInterface $groupRepository
     *
     * @return ProductRepository
     */
    public function setGroupRepository(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;

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
     * @return QueryBuilder
     */
    protected function buildByScope($scope)
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

        $rootEntity = current($qb->getRootEntities());
        $completenessMapping = $this->_em->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');
        $completenessClass = $completenessMapping['targetEntity'];
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
    public function findByIds(array $productIds)
    {
        $qb = $this->createQueryBuilder('Product');
        $this->addJoinToValueTables($qb);
        $rootAlias = current($qb->getRootAliases());
        $qb->andWhere(
            $qb->expr()->in($rootAlias.'.id', ':product_ids')
        );
        $qb->setParameter(':product_ids', $productIds);

        $query = $qb->getQuery();
        $query->useQueryCache(false);
        //$query->setQueryCacheDriver(null);
        //$query->useResultCache(false);

        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(GroupInterface $variantGroup, array $criteria = [])
    {
        $qb = $this->findAllForVariantGroupQB($variantGroup, $criteria);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p, f, v, pr, m, o, os')
            ->leftJoin('p.family', 'f')
            ->leftJoin('p.values', 'v')
            ->leftJoin('v.prices', 'pr')
            ->leftJoin('v.media', 'm')
            ->leftJoin('v.option', 'o')
            ->leftJoin('v.options', 'os')
            ->where('p.id=:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return [$this->attributeRepository->getIdentifierCode()];
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
                'Pim\Component\Catalog\Model\Association',
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
        $criteria = [
            'attribute'                              => $value->getAttribute(),
            $value->getAttribute()->getBackendType() => $value->getData()
        ];
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
        $sql = 'SELECT v.entity_id as product_id ' .
            'FROM %product_table% p ' .
            'INNER JOIN %product_value_table% v ON v.entity_id = p.id ' .
            'INNER JOIN pim_catalog_group_attribute ga ON ga.attribute_id = v.attribute_id AND ga.group_id = :groupId ' .
            'LEFT JOIN  pim_catalog_group_product gp ON gp.product_id = p.id ' .
            'AND gp.group_id IN ( ' .
            '    SELECT gr.id FROM pim_catalog_group gr ' .
            '    JOIN pim_catalog_group_type gr_type ON gr_type.id = gr.type_id AND gr_type.code = "VARIANT") ' .
            'WHERE gp.group_id = :groupId OR gp.group_id IS NULL ' .
            'AND ( ' .
            '    v.option_id IS NOT NULL ';


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
            'HAVING COUNT(ga.attribute_id) = ( SELECT COUNT(*) FROM pim_catalog_group_attribute WHERE group_id = :groupId) ';

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

        $query = $qb->getQuery();
        $query->useQueryCache(false);
        //$query->useResultCache(false);
        //$query->setQueryCacheDriver(null);

        $result = $query->execute();

        if (empty($result)) {
            return null;
        }

        return reset($result);
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
        $attributeIds = [];
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
        return $this->attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER]);
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

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productId, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.family', 'f')
            ->leftJoin('f.attributes', 'a')
            ->where('p.id = :id')
            ->andWhere('a.code = :code')
            ->setParameters([
                'id'   => $productId,
                'code' => $attributeCode,
            ])
            ->setMaxResults(1);

        return count($queryBuilder->getQuery()->getArrayResult()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInVariantGroup($productId, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('g.id')
            ->leftJoin('p.groups', 'g')
            ->where('p.id = :id')
            ->setParameters([
                'id' => $productId,
            ]);

        $groupIds = $queryBuilder->getQuery()->getScalarResult();

        $groupIds = array_reduce($groupIds, function ($carry, $item) {
            if (isset($item['id'])) {
                $carry[] = $item['id'];
            }

            return $carry;
        }, []);

        if (0 === count($groupIds)) {
            return false;
        }

        return $this->groupRepository->hasAttribute($groupIds, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function findProductIdsForVariantGroup(GroupInterface $variantGroup, array $criteria = [])
    {
        $qb = $this->findAllForVariantGroupQB($variantGroup, $criteria);
        $qb->select('Product.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $criteria
     *
     * @return QueryBuilder
     */
    protected function findAllForVariantGroupQB(GroupInterface $variantGroup, array $criteria = [])
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

        return $qb;
    }
}

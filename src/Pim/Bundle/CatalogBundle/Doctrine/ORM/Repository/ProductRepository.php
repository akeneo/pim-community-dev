<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.identifier IN (:identifiers)')
            ->setParameter('identifiers', $identifiers);

        return $qb->getQuery()->execute();
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
    public function getIdentifierProperties()
    {
        return ['identifier'];
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
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        return false;

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
    public function getEligibleProductsForVariantGroup($variantGroupId)
    {
        $variantGroup = $this->groupRepository->find($variantGroupId);
        if (null === $variantGroup || !$variantGroup->getType()->isVariant()) {
            return [];
        }

        $pqb = $this->queryBuilderFactory->create();
        foreach ($variantGroup->getAxisAttributes() as $axisAttribute) {
            $pqb->addFilter($axisAttribute->getCode(), Operators::IS_NOT_EMPTY, null);
        }
        $pqb->addFilter('variant_group', Operators::IS_EMPTY, null);

        return $pqb->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneById($id)
    {
        $pqb = $this->queryBuilderFactory->create();
        $pqb->addFilter('id', '=', $id);
        $result = $pqb->execute();

        if (0 === $result->count()) {
            return null;
        }

        return $result->current();
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

        //TODO - TIP-697: make the variant groups work again
        $qb->where('Product.identifier = :no_identifier');
        $qb->setParameter('no_identifier', 'THERE_IS_NO_SKU_LIKE_DAT');

        return $qb;

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

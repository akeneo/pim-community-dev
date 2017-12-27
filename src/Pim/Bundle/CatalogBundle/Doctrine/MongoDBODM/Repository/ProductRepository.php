<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface as MongoProductRepositoryInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AssociationRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Product repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository implements
    ProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    AssociationRepositoryInterface,
    MongoProductRepositoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $queryBuilderFactory;

    /** @var EntityManager */
    protected $entityManager;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var FamilyRepositoryInterface */
    protected $groupRepository;

    /**
     * Set the EntityManager
     *
     * @param EntityManager $entityManager
     *
     * @return ProductRepositoryInterface $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set the attribute repository
     *
     * @param AttributeRepositoryInterface $attributeRepository
     *
     * @return ProductRepositoryInterface
     */
    public function setAttributeRepository(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;

        return $this;
    }

    /**
     * Set the category repository
     *
     * @param CategoryRepositoryInterface $categoryRepository
     *
     * @return ProductRepositoryInterface
     */
    public function setCategoryRepository(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;

        return $this;
    }

    /**
     * Set family repository
     *
     * @param FamilyRepositoryInterface $familyRepository
     *
     * @return ProductRepositoryInterface
     */
    public function setFamilyRepository(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;

        return $this;
    }

    /**
     * Sets group repository
     *
     * @param GroupRepositoryInterface $groupRepository
     *
     * @return ProductRepositoryInterface
     */
    public function setGroupRepository(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $pqb = $this->queryBuilderFactory->create();
        $qb = $pqb->getQueryBuilder();
        $attribute = $this->getIdentifierAttribute();
        $pqb->addFilter($attribute->getCode(), '=', $identifier);
        $result = $qb->getQuery()->execute();

        return $result->getNext();
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

        return $result->getNext();
    }

    /**
     * {@inheritdoc}
     */
    public function buildByChannelAndCompleteness(ChannelInterface $channel)
    {
        $qb = $this->createQueryBuilder('p');
        foreach ($channel->getLocales() as $locale) {
            $qb->addOr(
                $qb
                    ->expr()
                    ->field(sprintf('normalizedData.completenesses.%s-%s', $channel->getCode(), $locale->getCode()))
                    ->equals(100)
            );
        }

        $categoryIds = $this->categoryRepository->getAllChildrenIds($channel->getCategory());
        $qb->addAnd(
            $qb->expr()->field('categoryIds')->in($categoryIds)
        );

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllIdsForAttribute(AttributeInterface $attribute)
    {
        $qb = $this->createQueryBuilder('p')
            ->hydrate(false)
            ->field('values.attribute')->equals((int) $attribute->getId())
            ->select('_id');

        $results = $qb->getQuery()->execute()->toArray();

        return array_keys($results);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllIdsForFamily(FamilyInterface $family)
    {
        $qb = $this->createQueryBuilder('p')
            ->hydrate(false)
            ->field('family')->equals($family->getId())
            ->select('_id');

        $results = $qb->getQuery()->execute()->toArray();

        return array_keys($results);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForCategory(CategoryInterface $category)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->field('categoryIds')->in([$category->getId()]);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForGroup(GroupInterface $group)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->field('groupIds')->in([$group->getId()]);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeFamilyRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('family')->equals($id)->unsetField()
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeAttributeRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('values.attribute')->equals($id)
            ->field('values')->pull(['attribute' => $id])
            ->getQuery(['w' => 0]) // do not wait, but no guarantee
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeCategoryRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('categoryIds')->in([$id])->pull($id)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeGroupRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('groups')->in([$id])->pull($id)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeAssociationTypeRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('associations.associationType')->equals($id)
            ->field('associations')->pull(['associationType' => $id])
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeAttributeOptionRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('values.option')->equals($id)
            ->field('values.$.option')->unsetField()
            ->getQuery()
            ->execute();

        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('values.optionIds')->in([$id])
            ->field('values.$.optionIds')->pull($id)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('p')->eagerCursor(true);
        $qb->field('_id')->in($ids);

        $cursor = $qb->getQuery()->execute();
        $products = [];
        foreach ($cursor as $product) {
            $products[] = $product;
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeChannelRemoval($id)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->multiple(true)
            ->field('completenesses.channel')->equals($id)
            ->field('completenesses')->pull(['channel' => $id])
            ->field('normalizedData.completenesses')->unsetField()
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(GroupInterface $variantGroup, array $criteria = [])
    {
        $qb = $this->findAllForVariantGroupQB($variantGroup, $criteria);
        $cursor = $qb->getQuery()->execute();

        $products = [];
        foreach ($cursor as $product) {
            $products[] = $product;
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByWithValues($id)
    {
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return [$this->attributeRepository->getIdentifierCode()];
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $productQueryBuilder = $this->queryBuilderFactory->create();
        $qb = $productQueryBuilder->getQueryBuilder();

        $productQueryBuilder->addFilter($value->getAttribute()->getCode(), '=', $value->getData());
        $result = $qb->hydrate(false)->getQuery()->getSingleResult();

        if (null === $result || (null !== $result && $value->getEntity()->getId() === (string) $result['_id'])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductQueryBuilderFactory(ProductQueryBuilderFactoryInterface $factory)
    {
        $this->queryBuilderFactory = $factory;

        return $this;
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
     * @return QueryBuilder
     */
    public function createGroupDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder();

        return $qb;
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     */
    public function createVariantGroupDatagridQueryBuilder(array $params = [])
    {
        $qb = $this->createQueryBuilder();

        if (isset($params['currentGroup'])) {
            $qb->field('_id')->in($this->getEligibleProductIdsForVariantGroup((int) $params['currentGroup']));
        }

        return $qb;
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     */
    public function createAssociationDatagridQueryBuilder(array $params = [])
    {
        $qb = $this->createQueryBuilder();

        if (isset($params['product'])) {
            $qb->field('_id')->notEqual($params['product']);
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getEligibleProductIdsForVariantGroup($variantGroupId)
    {
        $sql = 'SELECT ga.attribute_id, a.code '.
            'FROM pim_catalog_group_attribute ga '.
            'INNER JOIN pim_catalog_attribute a ON a.id = ga.attribute_id '.
            'WHERE ga.group_id = :groupId;';
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->execute();
        $attributes = $stmt->fetchAll();

        $qb = $this->createQueryBuilder()->hydrate(false)->select('_id');

        $otherVariantGroupsSQL = 'SELECT g.id as group_id ' .
            'FROM pim_catalog_group as g ' .
            'JOIN pim_catalog_group_type as gt on gt.id = g.type_id ' .
            'WHERE gt.code = "VARIANT" ' .
            'AND g.id != :groupId';

        $stmt = $this->entityManager->getConnection()->prepare($otherVariantGroupsSQL);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->execute();
        $otherVariantGroups = $stmt->fetchAll();

        $groupsToRemove = [];
        foreach ($otherVariantGroups as $variantGroup) {
            $groupsToRemove[] = (int) $variantGroup['group_id'];
        }
        $qb->addAnd($qb->expr()->field('groupIds')->notIn($groupsToRemove));

        foreach ($attributes as $attribute) {
            $andExpr = $qb
                ->expr()
                ->field(sprintf('normalizedData.%s', $attribute['code']))
                ->exists(true);

            $qb->addAnd($andExpr);
        }

        $result = $qb->getQuery()->execute()->toArray();

        $ids = [];
        foreach ($result as $item) {
            $ids[] = (string) $item['_id'];
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function countForAssociationType(AssociationTypeInterface $associationType)
    {
        $assocMatch = [
            '$and' => [
                ['associationType' => $associationType->getId()],
                [
                    '$or' => [
                        [
                            'products' => [ '$ne' => [] ]
                        ],
                        [
                            'groups' => [ '$ne' => [] ]
                        ]
                    ]
                ]
            ]
        ];

        $qb = $this->createQueryBuilder()
            ->hydrate(false)
            ->field('associations')->elemMatch($assocMatch)
            ->select('_id');

        return $qb->getQuery()->execute()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        $productIds = array_map(function ($id) {
            return new \MongoId($id);
        }, $productIds);

        $results = $this->getDocumentManager()
            ->getDocumentCollection($this->getDocumentName())
            ->aggregate([
                ['$match'  => ['_id' => ['$in' => $productIds]]],
                ['$unwind' => '$values'],
                ['$group'  => ['_id' => '$values.attribute']]
            ])->toArray();

        $ids = array_map(function ($result) {
            return $result['_id'];
        }, $results);

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProducts(array $productIds, array $attributeIds = [])
    {
        $qb = $this->createQueryBuilder('p');
        $qb->field('_id')->in($productIds);

        $cursor = $qb->getQuery()->execute();

        return $cursor->toArray();
    }

    /**
     * {@inheritdoc}
     *
     * TODO: find a way to do it efficiently
     */
    public function findByProductAndOwnerIds(ProductInterface $product, array $ownerIds)
    {
        $ownerIds = array_map(
            function ($id) {
                return new \MongoId($id);
            },
            $ownerIds
        );

        // retrieve products whom associations are concerned
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('associations')
            ->field('_id')->in($ownerIds)
            ->field('associations.products.$id')->equals(new \MongoId($product->getId()));

        $products = $qb->getQuery()->execute();
        $associations = [];

        // filter associations
        foreach ($products as $dummyProduct) {
            foreach ($dummyProduct->getAssociations() as $association) {
                if ($association->hasProduct($product)) {
                    $associations[] = $association;
                }
            }
        }

        return $associations;
    }

    /**
     * {@inheritdoc}
     *
     * @TODO: Make some refactoring with PublishedProductRepository
     */
    public function removeAssociatedProduct($productId, $assocTypeCount)
    {
        $mongoRef = [
            '$ref' => $this->dm->getClassMetadata($this->documentName)->getCollection(),
            '$id'  => new \MongoId($productId),
            '$db'  => $this->dm->getConfiguration()->getDefaultDB(),
        ];

        $collection = $this->dm->getDocumentCollection($this->documentName);

        $findQuery = ['associations.products' => $mongoRef];

        $updateQuery = [
            '$pull' => [
                'associations.$.products' => $mongoRef
            ]
        ];

        $updateOptions = [ 'multiple' => 1 ];

        // we iterate over the number of association types because the query removes only the product that
        // belongs to the first association (instead of removing it in existing associations)
        for ($i = 0; $i < $assocTypeCount; $i++) {
            $collection->update($findQuery, $updateQuery, $updateOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectManager()
    {
        return $this->getDocumentManager();
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
        $qb = $this->createQueryBuilder('p')
            ->field('groupIds')->in([$group->getId()])
            ->limit($maxResults);

        $products = $qb->getQuery()->execute()->toArray();

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByGroup(GroupInterface $group)
    {
        $qb = $this->createQueryBuilder('p')
            ->hydrate(false)
            ->field('groupIds')->in([$group->getId()]);

        $count = $qb->getQuery()->execute()->count();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('p')->hydrate(false);
        $count = $qb->getQuery()->execute()->count();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function findProductIdsForVariantGroup(GroupInterface $variantGroup, array $criteria = [])
    {
        $qb = $this->findAllForVariantGroupQB($variantGroup, $criteria);
        $qb
            ->select('_id')
            ->hydrate(false);

        $cursor = $qb->getQuery()->execute();

        $products = [];
        foreach ($cursor as $product) {
            $product['id'] = (string) $product['_id'];
            $products[] = $product;
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productId, $attributeCode)
    {
        $product = $this->getProductAsArray($productId);

        if (!isset($product['family'])) {
            return false;
        }

        return $this->familyRepository->hasAttribute($product['family'], $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInVariantGroup($productId, $attributeCode)
    {
        $product = $this->getProductAsArray($productId);

        if (!isset($product['groupIds'])) {
            return false;
        }

        return $this->groupRepository->hasAttribute($product['groupIds'], $attributeCode);
    }

    /**
     * @param mixed $productId
     *
     * @return array
     */
    protected function getProductAsArray($productId)
    {
        $query = $this->createQueryBuilder()
            ->field('_id')->equals($productId)
            ->hydrate(false)
            ->getQuery();

        return $query->getSingleResult();
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $criteria
     *
     * @return array
     */
    protected function findAllForVariantGroupQB(GroupInterface $variantGroup, array $criteria = [])
    {
        $qb = $this->createQueryBuilder()->eagerCursor(true);

        $qb->field('groupIds')->in([$variantGroup->getId()]);

        foreach ($criteria as $item) {
            $match = ['attribute' => (int) $item['attribute']->getId()];

            if (isset($item['option'])) {
                $match['option'] = $item['option']->getId();
            } elseif (isset($item['referenceData'])) {
                $match[$item['referenceData']['name']] = $item['referenceData']['data']->getId();
            }

            $qb->addAnd($qb->expr()->field('values')->elemMatch($match));
        }

        return $qb;
    }
}

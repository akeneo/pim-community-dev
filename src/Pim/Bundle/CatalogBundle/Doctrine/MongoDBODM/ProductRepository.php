<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface as MongoProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Product repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository in 1.4
 */
class ProductRepository extends DocumentRepository implements
    ProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    ReferableEntityRepositoryInterface,
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

        $qb->field('groups')->in([$group->getId()]);

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
            ->getQuery()
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
    public function findAllForVariantGroup(GroupInterface $variantGroup, array $criteria = array())
    {
        $qb = $this->createQueryBuilder()->eagerCursor(true);

        $qb->field('groupIds')->in([$variantGroup->getId()]);

        foreach ($criteria as $item) {
            $andExpr = $qb
                ->expr()
                ->field('values')
                ->elemMatch(['attribute' => (int) $item['attribute']->getId(), 'option' => $item['option']->getId()]);

            $qb->addAnd($andExpr);
        }

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
    public function findAllWithAttribute(AttributeInterface $attribute)
    {
        return $this->createQueryBuilder('p')
            ->field('values.attribute')->equals((int) $attribute->getId())
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithAttributeOption(AttributeOptionInterface $option)
    {
        $id = (int) $option->getId();
        $qb = $this->createQueryBuilder('p');

        if ('options' === $option->getAttribute()->getBackendType()) {
            $qb->field('values.optionIds')->in([$id]);
        } else {
            $qb->field('values.option')->equals($id);
        }

        return $qb
            ->getQuery()
            ->execute()
            ->toArray();
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
        return array($this->attributeRepository->getIdentifierCode());
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $productQueryBuilder = $this->queryBuilderFactory->create();
        $qb = $productQueryBuilder->getQueryBuilder();

        $productQueryBuilder->addFilter($value->getAttribute()->getCode(), '=', $value->getData());
        $result = $qb->hydrate(false)->getQuery()->execute();

        if (0 === $result->count() ||
            (1 === $result->count() && $value->getEntity()->getId() === (string) $result->getNext()['_id'])
        ) {
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
    public function createVariantGroupDatagridQueryBuilder(array $params = array())
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
    public function createAssociationDatagridQueryBuilder(array $params = array())
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
        $sql = 'SELECT ga.attribute_id '.
            'FROM pim_catalog_group_attribute ga '.
            'WHERE ga.group_id = :groupId;';
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->execute();
        $attributes = $stmt->fetchAll();

        $qb = $this->createQueryBuilder()->hydrate(false)->select('_id');

        // TODO (JJ) this whole statement is bad, we should be able to do this
        // without another request, see with Benoit
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
                ->field('values')
                ->elemMatch(['attribute' => (int) $attribute['attribute_id'], 'option' => ['$exists' => true]]);

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
                        [ 'products' => [ '$ne' => [] ] ],
                        [ 'groups'   => [ '$ne' => [] ] ]
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
        $qb = $this->createQueryBuilder('p');
        $qb
            ->field('_id')->in($productIds)
            ->distinct('values.attribute')
            ->hydrate(false);

        $cursor = $qb->getQuery()->execute();

        return $cursor->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProducts(array $productIds, array $attributeIds = array())
    {
        $qb = $this->createQueryBuilder('p');
        $qb->field('_id')->in($productIds);

        $cursor = $qb->getQuery()->execute();

        return $cursor->toArray();
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
            '$id' => new \MongoId($productId),
            '$db' => $this->dm->getConfiguration()->getDefaultDB(),
        ];

        $collection = $this->dm->getDocumentCollection($this->documentName);

        // we iterate over the number of association types because the query removes only the product that
        // belongs to the first association (instead of removing it in existing associations)
        for ($i = 0; $i < $assocTypeCount; $i++) {
            $collection->update(
                [
                    'associations' => [
                        '$elemMatch' => [
                            'products' => $mongoRef
                        ]
                    ]
                ],
                [
                    '$pull' => [
                        'associations.$.products' => $mongoRef
                    ]
                ],
                [ 'multiple' => 1 ]
            );
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
        return $this->attributeRepository->findOneBy(['attributeType' => 'pim_catalog_identifier']);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function getReferenceProperties()
    {
        return $this->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function findByReference($code)
    {
        return $this->findOneByIdentifier($code);
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Product repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository implements
    ProductRepositoryInterface,
    ReferableEntityRepositoryInterface,
    AssociationRepositoryInterface
{
    /** @var ProductQueryFactoryInterface */
    protected $productQueryFactory;

    /** @var EntityManager */
    protected $entityManager;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /**
     * Set the EntityManager
     *
     * @param EntityManager $entityManager
     *
     * @return ProductRepository $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set the attribute repository
     *
     * @param AttributeRepository $attributeRepository
     *
     * @return ProductRepository
     */
    public function setAttributeRepository(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;

        return $this;
    }

    /**
     * Set the category repository
     *
     * @param CategoryRepository $categoryRepository
     *
     * @return ProductRepository
     */
    public function setCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;

        return $this;
    }

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
        $qb = $this->findAllByAttributesQB($attributes, $criteria, $orderBy, $limit, $offset);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $pqb = $this->productQueryFactory->create();
        $qb = $pqb->getQueryBuilder();

        foreach ($criteria as $field => $data) {
            // TODO : fix the calls to this method, no need to pass the attribute object in data, pass only the value
            if (is_array($data)) {
                $attribute = $data['attribute'];
                $field = $attribute->getCode();
                $data = $data['value'];
            }
            $pqb->addFilter($field, '=', $data);
        }

        $result = $qb->getQuery()->execute();

        if ($result->count() > 1) {
            throw new \LogicException(
                sprintf(
                    'Many products have been found that match criteria:' . "\n" . '%s',
                    print_r($criteria, true)
                )
            );
        }

        return $result->getNext();
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
     * @param AbstractAttribute $attribute
     *
     * @return string[]
     */
    public function findAllIdsForAttribute(AbstractAttribute $attribute)
    {
        $qb = $this->createQueryBuilder('p')
            ->hydrate(false)
            ->field('values.attribute')->equals((int) $attribute->getId())
            ->select('_id');

        $results = $qb->getQuery()->execute()->toArray();

        return array_keys($results);
    }

    /**
     * @param Family $family
     *
     * @return string[]
     */
    public function findAllIdsForFamily(Family $family)
    {
        $qb = $this->createQueryBuilder('p')
            ->hydrate(false)
            ->field('family')->equals($family->getId())
            ->select('_id');

        $results = $qb->getQuery()->execute()->toArray();

        return array_keys($results);
    }

    /**
     * @param CategoryInterface $category
     *
     * @return ProductInterface[]
     */
    public function findAllForCategory(CategoryInterface $category)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->field('categoryIds')->in([$category->getId()]);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Group $group
     *
     * @return ProductInterface[]
     */
    public function findAllForGroup(Group $group)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->field('groups')->in([$group->getId()]);

        return $qb->getQuery()->execute();
    }

    /**
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
    public function findByExistingFamily()
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
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
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array())
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
    public function findAllWithAttribute(AbstractAttribute $attribute)
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
    public function findAllWithAttributeOption(AttributeOption $option)
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
    public function findByReference($code)
    {
        return $this->findOneBy(
            [
                [
                    'attribute' => $this->attributeRepository->getIdentifier(),
                    'value' => $code,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array($this->attributeRepository->getIdentifierCode());
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $productQueryBuilder = $this->productQueryFactory->create();
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
    public function setProductQueryFactory($factory)
    {
        $this->productQueryFactory = $factory;

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
     * @param integer $variantGroupId
     *
     * @return array product ids
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
    public function countForAssociationType(AssociationType $associationType)
    {
        $assocMatch = [
            '$and' => [
                ['associationType' => $associationType->getId()],
                [
                    '$or' => [
                        [ 'products' => [ '$ne'=> [] ] ],
                        [ 'groups'   => [ '$ne'=> [] ] ]
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
     * @param FamilyRepository $familyRepository
     *
     * @return \Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepository
     */
    public function setFamilyRepository(FamilyRepository $familyRepository)
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
     * Finds documents by a set of criteria
     *
     * @param array        $attributes
     * @param array        $criteria
     * @param array        $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     *
     * @return QueryBuilder
     *
     * @throws \RuntimeException
     */
    protected function findAllByAttributesQB(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $qb = $this->createQueryBuilder('p');

        foreach ($attributes as $attribute => $value) {
            $qb->field($attribute)->equals($value);
        }

        if ($criteria) {
            foreach ($criteria as $field => $value) {
                $qb->field('normalizedData.'.$field)->equals($value);
            }
        }

        if ($orderBy) {
            throw new \RuntimeException("Order by is not implemented yet ! ".__CLASS__."::".__METHOD__);
        }

        if ($limit) {
            throw new \RuntimeException("Limit is not implemented yet ! ".__CLASS__."::".__METHOD__);
        }

        if ($offset) {
            throw new \RuntimeException("Offset is not implemented yet ! ".__CLASS__."::".__METHOD__);
        }

        return $qb;
    }

    /**
     * @param integer $productId
     * @param integer $assocTypeCount
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
}

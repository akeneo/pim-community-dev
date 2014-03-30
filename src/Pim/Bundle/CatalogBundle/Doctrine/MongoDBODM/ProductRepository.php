<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

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
    /**
     * @param ProductQueryBuilder
     */
    protected $productQB;

    /**
     * ORM EntityManager to access ORM entities
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Category class
     *
     * @var string
     */
    protected $categoryClass;

    /** @var string */
    protected $identifier;

    /** @var string */
    protected $attributeClass;

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
     * Set the Category class
     *
     * @param string $categoryClass
     *
     * @return ProductRepository $this
     */
    public function setCategoryClass($categoryClass)
    {
        $this->categoryClass = $categoryClass;
    }

    /**
     * Set the attribute class
     *
     * @param string $attributeClass
     *
     * @return ProductRepository $this
     */
    public function setAttributeClass($attributeClass)
    {
        $this->attributeClass = $attributeClass;

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

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('p');
        $pqb = $this->getProductQueryBuilder($qb);
        foreach ($criteria as $field => $data) {
            if (is_array($data)) {
                $pqb->addAttributeFilter($data['attribute'], '=', $data['value']);
            } else {
                $pqb->addFieldFilter($field, '=', $data);
            }
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
    public function buildByChannelAndCompleteness(Channel $channel)
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

        $qb->field('categories')->in([$category->getId()]);

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
            ->field('categories')->in([$id])->pull($id)
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
            ->field('values.options')->in([$id])
            ->field('values.$.options')->pull($id)
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
            $products[]= $product;
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array())
    {
        $qb = $this->createQueryBuilder()->eagerCursor(true);

        $qb->field('groups')->in([$variantGroup->getId()]);

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
    public function getFullProduct($id)
    {
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        $categories = $product->getCategories();
        $categoryIds = array();
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        $categoryRepository = $this->entityManager->getRepository($this->categoryClass);

        $categoryTable = $this->entityManager->getClassMetadata($this->categoryClass)->getTableName();

        $categoryIds = implode(',', $categoryIds);

        if (!empty($categoryIds)) {
            $sql = "SELECT".
                   "    tree.id AS tree_id,".
                   "    COUNT(category.id) AS product_count".
                   "  FROM $categoryTable tree".
                   "  LEFT JOIN $categoryTable category".
                   "    ON category.root = tree.id".
                   " AND category.id IN ($categoryIds)".
                   " WHERE tree.parent_id IS NULL".
                   " GROUP BY tree.id";
        } else {
            $sql = "SELECT".
                   "    tree.id AS tree_id,".
                   "    '0' AS product_count".
                   "  FROM $categoryTable tree".
                   "  LEFT JOIN $categoryTable category".
                   "    ON category.root = tree.id".
                   " WHERE tree.parent_id IS NULL".
                   " GROUP BY tree.id";
        }

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();

        $productCounts = $stmt->fetchAll();
        $trees = array();
        foreach ($productCounts as $productCount) {
            $tree = array();
            $tree['productCount'] = $productCount['product_count'];
            $tree['tree'] = $categoryRepository->find($productCount['tree_id']);
            $trees[] = $tree;
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        $products = $this->getProductIdsInCategories($categoryIds);

        return array_keys(iterator_to_array($products));
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        return $this->getProductsCountInCategories($categoryIds);
    }

    /**
     * Return categories ids provided by the categoryQb or by the provided category
     *
     * @param CategoryInterface $category
     * @param OrmQueryBuilder   $categoryQb
     *
     * @return array $categoryIds
     */
    protected function getCategoryIds(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = array();

        if (null !== $categoryQb) {
            $categoryAlias = $categoryQb->getRootAlias();
            $categories = $categoryQb->select('PARTIAL '.$categoryAlias.'.{id}')->getQuery()->getArrayResult();
        } else {
            $categories = array(array('id' => $category->getId()));
        }

        foreach ($categories as $category) {
            $categoryIds[] = $category['id'];
        }

        return $categoryIds;
    }

    /**
     * Return a cursor on the product ids belonging the categories
     * with category ids provided
     *
     * @param array $categoryIds
     *
     * @return Cursor mongoDB cursor on the Ids
     */
    public function getProductIdsInCategories(array $categoryIds)
    {
        if (count($categoryIds) === 0) {
            return 0;
        }

        $qb = $this->createQueryBuilder()
            ->hydrate(false)
            ->field('categories')->in($categoryIds)
            ->select('_id');

        return $qb->getQuery()->execute();
    }

    /**
     * Return the number of products matching the categories ids provided
     *
     * @param array $categoriesIds
     *
     * @return int $productsCount
     */
    public function getProductsCountInCategories(array $categoriesIds)
    {
        return $this->getProductIdsInCategories($categoriesIds)->count();
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
                    'attribute' => $this->getIdentifier(),
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
        return array($this->getIdentifier()->getCode());
    }

    /**
     * Returns the identifier code
     *
     * @return string
     */
    public function getIdentifier()
    {
        if (!isset($this->identifier)) {
            if (!$this->entityManager) {
                throw new \LogicException('Entity Manager must be set before getting reference properties');
            }

            $this->identifier = $this->entityManager
                ->createQuery(
                    sprintf(
                        'SELECT a FROM %s a WHERE a.attributeType=:identifier_type ',
                        $this->attributeClass
                    )
                )
                ->setParameter('identifier_type', 'pim_catalog_identifier')
                ->getSingleResult();
        }

        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $qb = $this->createQueryBuilder();
        $this->applyFilterByAttribute($qb, $value->getAttribute(), $value->getData());
        $result = $qb->hydrate(false)->getQuery()->getSingleResult();

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
    public function setProductQueryBuilder($productQB)
    {
        $this->productQB = $productQB;

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    protected function getProductQueryBuilder($qb)
    {
        if (!$this->productQB) {
            throw new \LogicException('Product query builder must be configured');
        }

        $this->productQB->setQueryBuilder($qb);

        return $this->productQB;
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
    public function applyFilterByAttribute($qb, AbstractAttribute $attribute, $value, $operator = '=')
    {
        $this->getProductQueryBuilder($qb)->addAttributeFilter($attribute, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByField($qb, $field, $value, $operator = '=')
    {
        $this->getProductQueryBuilder($qb)->addFieldFilter($field, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, array $productIds, $include)
    {
        if ($include) {
            $qb->addAnd($qb->expr()->field('id')->in($productIds));
        } else {
            $qb->addAnd($qb->expr()->field('id')->notIn($productIds));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByAttribute($qb, AbstractAttribute $attribute, $direction)
    {
        $this->getProductQueryBuilder($qb)->addAttributeSorter($attribute, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByField($qb, $field, $direction)
    {
        $this->getProductQueryBuilder($qb)->addFieldSorter($field, $direction);
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
    public function deleteFromIds(array $ids)
    {
        if (empty($ids)) {
            throw new \LogicException('No products to remove');
        }

        $qb = $this->createQueryBuilder('p');
        $qb
            ->remove()
            ->field('_id')->in($ids);

        $result = $qb->getQuery()->execute();

        return $result['n'];
    }

    /**
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, $values)
    {
        // manage inset for selected entities
        if ($values) {
            $qb->field('_id');
            $inset ? $qb->in($values) : $qb->notIn($values);
        }

        // remove limit of the query
        $qb->limit(null);
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
     * {@inheritdoc}
     *
     * TODO: Take in account family attributes
     */
    public function findCommonAttributeIds(array $productIds)
    {
        $collection = $this->dm->getDocumentCollection($this->documentName);

        $expr = new Expr($this->dm);
        $expr->setClassMetadata($this->class);
        $expr->field('_id')->in($productIds);

        $pipeline = array(
            array(
                '$match'   => $expr->getQuery()
            ),
            array('$unwind' => '$values'),
            array(
                '$group'  => array(
                    '_id'       => '$_id',
                    'attribute' => array( '$addToSet' => '$values.attribute')
                )
            ),
            array('$unwind' => '$attribute'),
            array('$group'  => array(
                '_id'   => '$attribute',
                'count' => array('$sum' => 1)
            )),
            array('$match'   => array('count' => count($productIds))),
            array('$project' => array('values.attribute' => 1))
        );

        $results = $collection->aggregate($pipeline)->toArray();

        $attributeIds = array();
        foreach ($results as $result) {
            $attributeIds[] = $result['_id'];
        }

        return $attributeIds;
    }
}

<?php

namespace Akeneo\Bundle\ClassificationBundle\Doctrine\Mongo\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;

/**
 * Item category repository
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractItemCategoryRepository implements
    ItemCategoryRepositoryInterface,
    CategoryFilterableRepositoryInterface
{
    /** @var EntityManager ORM EntityManager to access ORM entities */
    protected $em;

    /** @var DocumentManager MongoDBODM Document Manager to access ODM entities */
    protected $documentManager;

    /** @var string */
    protected $documentName;

    /** @var string Category class */
    protected $categoryClass;

    /**
     * @param DocumentManager $docManager
     * @param string          $documentName
     * @param EntityManager   $em
     * @param string          $categoryClass
     */
    public function __construct(DocumentManager $docManager, $documentName, EntityManager $em, $categoryClass)
    {
        $this->documentManager = $docManager;
        $this->documentName    = $documentName;
        $this->em              = $em;
        $this->categoryClass   = $categoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByUnclassified($qb)
    {
        $qb->addAnd($qb->expr()->field('categoryIds')->size(0));
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIds($qb, array $categoryIds, $include = true)
    {
        if ($include) {
            $qb->addAnd($qb->expr()->field('categoryIds')->in($categoryIds));
        } else {
            $qb->addAnd($qb->expr()->field('categoryIds')->notIn($categoryIds));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIdsOrUnclassified($qb, array $categoryIds)
    {
        $qb->addAnd(
            $qb->expr()
                ->addOr($qb->expr()->field('categoryIds')->in($categoryIds))
                ->addOr($qb->expr()->field('categoryIds')->size(0))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCountByTree($item)
    {
        $categories = $item->getCategories();
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        $categoryRepository = $this->em->getRepository($this->categoryClass);
        $categoryTable = $this->em->getClassMetadata($this->categoryClass)->getTableName();
        $categoryIds = implode(',', $categoryIds);

        $sql = "SELECT tree.id AS tree_id, ";
        $sql.= ((!empty($categoryIds)) ? "COUNT(category.id)" : "0") . " AS item_count ".
               "FROM $categoryTable tree " .
               "LEFT JOIN $categoryTable category ON category.root = tree.id ";
        $sql.= (!empty($categoryIds)) ? " AND category.id IN ($categoryIds) " : "";
        $sql.= " WHERE tree.parent_id IS NULL".
               " GROUP BY tree.id";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $itemCounts = $stmt->fetchAll();

        $trees = [];
        foreach ($itemCounts as $itemCount) {
            $trees[] = [
                'itemCount' => $itemCount['item_count'],
                'tree'      => $categoryRepository->find($itemCount['tree_id']),
            ];
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemIdsInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        $items = $this->getItemIdsInCategories($categoryIds);

        return array_keys(iterator_to_array($items));
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        return $this->getItemIdsInCategories($categoryIds)->count();
    }

    /**
     * Return a cursor on the item ids belonging the categories
     * with category ids provided
     *
     * @param array $categoryIds
     *
     * @return Cursor mongoDB cursor on the Ids
     */
    public function getItemIdsInCategories(array $categoryIds)
    {
        if (empty($categoryIds)) {
            return 0;
        }

        $qb = $this->documentManager->createQueryBuilder($this->documentName)
            ->hydrate(false)
            ->field('categoryIds')->in($categoryIds)
            ->select('_id');

        return $qb->getQuery()->execute();
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
        $categoryIds = [];

        if (null !== $categoryQb) {
            $categoryAlias = $categoryQb->getRootAlias();
            $categories = $categoryQb->select('PARTIAL '.$categoryAlias.'.{id}')->getQuery()->getArrayResult();
        } else {
            $categories = [['id' => $category->getId()]];
        }

        foreach ($categories as $category) {
            $categoryIds[] = $category['id'];
        }

        return $categoryIds;
    }
}

<?php

namespace Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Item category repository
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractItemCategoryRepository implements
    ItemCategoryRepositoryInterface,
    CategoryFilterableRepositoryInterface
{
    /** @var string */
    protected $entityName;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function findCategoriesItem($item): array
    {
        $config = $this->getMappingConfig($item);

        $sql = sprintf(
            'SELECT DISTINCT(category.id) ' .
            'FROM %s category ' .
            'INNER JOIN %s category_item ON category_item.category_id = category.id ' .
            'AND category_item.%s = :itemId ',
            $config['categoryTable'],
            $config['categoryAssocTable'],
            $config['relation']
        );

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('itemId', $item->getId());

        $stmt->execute();
        $categories = [];
        foreach ($stmt->fetchAll() as $categoryId) {
            $categories[] = $this->em->getRepository($config['categoryClass'])->find($categoryId['id']);
        }

        return $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCountByTree($item)
    {
        $config = $this->getMappingConfig($item);

        $sql = sprintf(
            'SELECT COUNT(DISTINCT category_item.category_id) AS item_count, tree.id AS tree_id ' .
            'FROM %s tree ' .
            'JOIN %s category ON category.root = tree.id ' .
            'LEFT JOIN %s category_item ON category_item.category_id = category.id ' .
            'AND category_item.%s= :itemId ' .
            'GROUP BY tree.id',
            $config['categoryTable'],
            $config['categoryTable'],
            $config['categoryAssocTable'],
            $config['relation']
        );

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('itemId', $item->getId());

        $stmt->execute();
        $items = $stmt->fetchAll();

        return $this->buildItemCountByTree($items, $config['categoryClass']);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(array $categoryIds = [])
    {
        if (empty($categoryIds)) {
            return 0;
        }

        $qb = $this->em->createQueryBuilder();

        return $qb
            ->select($qb->expr()->count('distinct i'))
            ->from($this->entityName, 'i')
            ->join('i.categories', 'node')
            ->where('node.id IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByUnclassified($qb)
    {
        $this->joinQueryBuilderOnCategories($qb);

        $qb->andWhere($qb->expr()->isNull(CategoryFilterableRepositoryInterface::JOIN_ALIAS . '.id'));
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIds($qb, array $categoryIds, $include = true)
    {
        $rootAlias = $qb->getRootAlias();
        $filterCatIds = uniqid('filterCatIds');

        if ($include) {
            $this->joinQueryBuilderOnCategories($qb);
            $qb->andWhere($qb->expr()->in(
                CategoryFilterableRepositoryInterface::JOIN_ALIAS . '.id',
                ':' . $filterCatIds
            ));
        } else {
            $alias = uniqid('filterCategory');
            $rootAliasIn = uniqid($rootAlias);
            $rootEntity = current($qb->getRootEntities());
            $qbIn = $qb->getEntityManager()->createQueryBuilder();
            $qbIn
                ->select($rootAliasIn.'.id')
                ->from($rootEntity, $rootAliasIn, $rootAliasIn . '.id')
                ->innerJoin($rootAliasIn . '.categories', $alias)
                ->where($qbIn->expr()->in($alias . '.id', ':' . $filterCatIds));

            $qb->andWhere($qb->expr()->notIn($rootAlias . '.id', $qbIn->getDQL()));
        }
        $qb->setParameter($filterCatIds, $categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIdsOrUnclassified($qb, array $categoryIds)
    {
        $filterCatIds = uniqid('filterCatIdsOrUnclassified');

        $this->joinQueryBuilderOnCategories($qb);

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in(CategoryFilterableRepositoryInterface::JOIN_ALIAS . '.id', ':' . $filterCatIds),
                $qb->expr()->isNull(CategoryFilterableRepositoryInterface::JOIN_ALIAS . '.id')
            )
        );

        $qb->setParameter($filterCatIds, $categoryIds);
    }

    /**
     * Build array of item with item count by category and category entity
     *
     * @param array  $itemCounts
     * @param string $categoryClass
     *
     * @return array
     */
    protected function buildItemCountByTree(array $itemCounts, $categoryClass)
    {
        $trees = [];
        foreach ($itemCounts as $itemCount) {
            $trees[] = [
                'itemCount' => $itemCount['item_count'],
                'tree'      => $this->em->getRepository($categoryClass)->find($itemCount['tree_id']),
            ];
        }

        return $trees;
    }

    /**
     * Get mapping information to build SQL query
     *
     * @param $item
     *
     * @return array
     */
    protected function getMappingConfig($item)
    {
        $itemMetadata = $this->em->getClassMetadata(ClassUtils::getClass($item));

        $categoryAssoc = $itemMetadata->getAssociationMapping('categories');
        $categoryClass = $categoryAssoc['targetEntity'];

        return [
            'categoryClass'      => $categoryAssoc['targetEntity'],
            'categoryTable'      => $this->em->getClassMetadata($categoryClass)->getTableName(),
            'categoryAssocTable' => $categoryAssoc['joinTable']['name'],
            'relation'           => key($categoryAssoc['relationToSourceKeyColumns']),
        ];
    }

    /**
     * @param mixed $qb
     */
    protected function joinQueryBuilderOnCategories($qb)
    {
        $joins = $qb->getDqlPart('join');
        $rootAlias = $qb->getRootAlias();

        // Ensure that we did not joined it already
        if (isset($joins[$rootAlias])) {
            foreach ($joins[$rootAlias] as $join) {
                if (CategoryFilterableRepositoryInterface::JOIN_ALIAS === $join->getAlias()) {
                    return;
                }
            }
        }

        $qb->leftJoin($qb->getRootAlias().'.categories', CategoryFilterableRepositoryInterface::JOIN_ALIAS);
    }
}

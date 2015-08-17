<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface as CatalogCategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Pim\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepository implements
    ProductCategoryRepositoryInterface,
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
        $this->em         = $em;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        return $this->getItemCountByTree($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CatalogCategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        return $this->getItemsCountInCategory($category, $categoryQb);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CatalogCategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        return $this->getItemIdsInCategory($category, $categoryQb);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, array $productIds, $include)
    {
        $rootAlias  = $qb->getRootAlias();
        if ($include) {
            $expression = $qb->expr()->in($rootAlias.'.id', $productIds);
            $qb->andWhere($expression);
        } else {
            $expression = $qb->expr()->notIn($rootAlias.'.id', $productIds);
            $qb->andWhere($expression);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByUnclassified($qb)
    {
        $rootAlias = $qb->getRootAlias();
        $alias     = uniqid('filterCategory');

        $qb->leftJoin($rootAlias.'.categories', $alias);
        $qb->andWhere($qb->expr()->isNull($alias . '.id'));
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIds($qb, array $categoryIds, $include = true)
    {
        $rootAlias    = $qb->getRootAlias();
        $alias        = uniqid('filterCategory');
        $filterCatIds = uniqid('filterCatIds');

        if ($include) {
            $qb->leftJoin($rootAlias.'.categories', $alias);
            $qb->andWhere($qb->expr()->in($alias.'.id', ':' . $filterCatIds));
        } else {
            $rootAliasIn = uniqid($rootAlias);
            $rootEntity = current($qb->getRootEntities());
            $qbIn = $qb->getEntityManager()->createQueryBuilder();
            $qbIn
                ->select($rootAliasIn.'.id')
                ->from($rootEntity, $rootAliasIn, $rootAliasIn . '.id')
                ->leftJoin($rootAliasIn . '.categories', $alias)
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
        $rootAlias    = $qb->getRootAlias();
        $alias        = uniqid('filterCategory');
        $filterCatIds = uniqid('filterCatIdsOrUnclassified');

        $qb->leftJoin($rootAlias . '.categories', $alias);
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in($alias . '.id', ':' . $filterCatIds),
                $qb->expr()->isNull($alias . '.id')
            )
        );
        $qb->setParameter($filterCatIds, $categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCountByTree($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected a "Pim\Bundle\CatalogBundle\Model\ProductInterface", got a "%s"',
                    ClassUtils::getClass($product)
                )
            );
        }

        $productMetadata = $this->em->getClassMetadata(get_class($product));

        $categoryAssoc = $productMetadata->getAssociationMapping('categories');

        $categoryClass = $categoryAssoc['targetEntity'];
        $categoryTable = $this->em->getClassMetadata($categoryClass)->getTableName();

        $categoryAssocTable = $categoryAssoc['joinTable']['name'];

        $sql = "SELECT".
            "    tree.id AS tree_id,".
            "    COUNT(category_product.product_id) AS item_count".
            "  FROM $categoryTable tree".
            "  JOIN $categoryTable category".
            "    ON category.root = tree.id".
            "  LEFT JOIN $categoryAssocTable category_product".
            "    ON category_product.product_id = :productId".
            "   AND category_product.category_id = category.id".
            " GROUP BY tree.id";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('productId', $product->getId());

        $stmt->execute();
        $productCounts = $stmt->fetchAll();
        $trees = [];
        $categoryRepo = $this->em->getRepository($categoryClass);
        foreach ($productCounts as $productCount) {
            $tree = [];
            $tree['itemCount'] = $productCount['item_count'];
            $tree['tree'] = $categoryRepo->find($productCount['tree_id']);
            $trees[] = $tree;
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemIdsInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('DISTINCT p.id');
        $qb->from($this->entityName, 'p', 'p.id');
        $qb->join('p.categories', 'node');

        if (null === $categoryQb) {
            $qb->where('node.id = :nodeId');
            $qb->setParameter('nodeId', $category->getId());
        } else {
            $qb->where($categoryQb->getDqlPart('where'));
            $qb->setParameters($categoryQb->getParameters());
        }

        $products = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        return array_keys($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($qb->expr()->count('distinct p'));
        $qb->from($this->entityName, 'p');
        $qb->join('p.categories', 'node');

        if (null === $categoryQb) {
            $qb->where('node.id = :nodeId');
            $qb->setParameter('nodeId', $category->getId());
        } else {
            $qb->where($categoryQb->getDqlPart('where'));
            $qb->setParameters($categoryQb->getParameters());
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}

<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetCategoryRepositoryInterface;

/**
 * Asset category repository
 *
 * TODO: Find a way to "merge" this class with the existing one for products to avoid duplicate code
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AssetCategoryRepository implements AssetCategoryRepositoryInterface
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
     * {@inherit}
     */
    public function getItemCountByTree($asset)
    {
        $productMetadata = $this->em->getClassMetadata(get_class($asset));

        $categoryAssoc = $productMetadata->getAssociationMapping('categories');

        $categoryClass = $categoryAssoc['targetEntity'];
        $categoryTable = $this->em->getClassMetadata($categoryClass)->getTableName();

        $categoryAssocTable = $categoryAssoc['joinTable']['name'];

        $sql = "SELECT" .
            "    tree.id AS tree_id," .
            "    COUNT(category_asset.asset_id) AS item_count" .
            "  FROM $categoryTable tree" .
            "  JOIN $categoryTable category" .
            "    ON category.root = tree.id" .
            "  LEFT JOIN $categoryAssocTable category_asset" .
            "    ON category_asset.asset_id = :assetId" .
            "   AND category_asset.category_id = category.id" .
            " GROUP BY tree.id";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('assetId', $asset->getId());

        $stmt->execute();
        $assetCounts = $stmt->fetchAll();
        $trees = array();
        foreach ($assetCounts as $assetCount) {
            $tree = array();
            $tree['itemCount'] = $assetCount['item_count'];
            $tree['tree'] = $this->em->getRepository($categoryClass)->find($assetCount['tree_id']);
            $trees[] = $tree;
        }

        return $trees;
    }

    /**
     * {@inherit}
     */
    public function getItemIdsInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null)
    {
        // TODO: Implement getItemIdsInCategory() method.
    }

    /**
     * {@inherit}
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

    /**
     * {@inheritdoc}
     *
     * TODO: Those are exactly the same methods as in
     *       Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductCategoryRepository
     *       We must find a way to avoid duplicate code for the 3 methods below
     */
    public function applyFilterByUnclassified($qb)
    {
        $rootAlias = $qb->getRootAlias();
        $alias     = uniqid('filterCategory');

        $qb->leftJoin($rootAlias . '.categories', $alias);
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
}

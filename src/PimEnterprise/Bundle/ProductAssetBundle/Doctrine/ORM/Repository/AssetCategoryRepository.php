<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetCategoryRepositoryInterface;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AssetCategoryRepository implements AssetCategoryRepositoryInterface, ItemCategoryRepositoryInterface
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

        $sql = "SELECT".
            "    tree.id AS tree_id,".
            "    COUNT(category_asset.asset_id) AS asset_count".
            "  FROM $categoryTable tree".
            "  JOIN $categoryTable category".
            "    ON category.root = tree.id".
            "  LEFT JOIN $categoryAssocTable category_asset".
            "    ON category_asset.asset_id = :assetId".
            "   AND category_asset.category_id = category.id".
            " GROUP BY tree.id";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('assetId', $asset->getId());

        $stmt->execute();
        $assetCounts = $stmt->fetchAll();
        $trees = array();
        foreach ($assetCounts as $assetCount) {
            $tree = array();
            $tree['productCount'] = $assetCount['asset_count'];
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
}

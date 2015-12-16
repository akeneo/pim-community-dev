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

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\AbstractItemCategoryRepository;
use Doctrine\DBAL\Types\Type;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetCategoryRepositoryInterface;

/**
 * Asset category repository
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AssetCategoryRepository extends AbstractItemCategoryRepository implements AssetCategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItemCountByGrantedTree(AssetInterface $asset, UserInterface $user)
    {
        $config = $this->getMappingConfig($asset);

        $sql = sprintf(
            'SELECT COUNT(DISTINCT category_item.category_id) AS item_count, tree.id AS tree_id ' .
            'FROM (SELECT id FROM %s where parent_id IS NULL) tree ' .
            'JOIN %s category ON category.root = tree.id ' .
            'LEFT JOIN %s category_item ON category_item.category_id = category.id ' .
            'AND category_item.%s= :item_id ' .
            'INNER JOIN pimee_security_asset_category_access a ON a.category_id = category.id ' .
            'AND a.view_items = 1 AND a.user_group_id IN (%s) ' .
            'GROUP BY tree.id',
            $config['categoryTable'],
            $config['categoryTable'],
            $config['categoryAssocTable'],
            $config['relation'],
            implode(',', $user->getGroupsIds())
        );

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('item_id', $asset->getId());
        $stmt->execute();
        $trees = $stmt->fetchAll();

        return $this->buildItemCountByTree($trees, $config['categoryClass']);
    }
}

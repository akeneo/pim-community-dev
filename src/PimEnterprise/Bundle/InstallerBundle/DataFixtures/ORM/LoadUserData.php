<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\DataFixtures\ORM;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadUserData as BaseLoadUserData;

/**
 * Load fixtures for users
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class LoadUserData extends BaseLoadUserData
{
    /**
     * {@inheritdoc}
     */
    protected function buildUser(array $data)
    {
        $user = parent::buildUser($data);

        $tree = $this->getAssetTree($data['default_asset_tree']);
        $user->setDefaultAssetTree($tree);

        return $user;
    }

    /**
     * Get tree entity from category code
     *
     * @param string $categoryCode
     *
     * @return CategoryInterface
     */
    protected function getAssetTree($categoryCode)
    {
        $categoryRepository = $this->container->get('pimee_product_asset.repository.category');
        $category           = $categoryRepository->findOneBy(['code' => $categoryCode, 'parent' => null]);

        return $category ? $category : current($categoryRepository->getTrees());
    }
}

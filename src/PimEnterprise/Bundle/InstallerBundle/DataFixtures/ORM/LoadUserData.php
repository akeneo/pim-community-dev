<?php

namespace PimEnterprise\Bundle\InstallerBundle\DataFixtures\ORM;

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
     * @return \PimEnterprise\Component\ProductAsset\Model\CategoryInterface
     */
    protected function getAssetTree($categoryCode)
    {
        $categoryRepository = $this->container->get('pimee_product_asset.repository.category');
        $category           = $categoryRepository->findOneBy(['code' => $categoryCode, 'parent' => null]);

        return $category ? $category : current($categoryRepository->getTrees());
    }
}

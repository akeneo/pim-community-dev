<?php

namespace Context;

use Context\Loader\ProductAssetLoader;

/**
 * A context for initializing catalog configuration
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseCatalogConfigurationContext extends CatalogConfigurationContext
{
    /**
     *{@inheritdoc}
     */
    public function aCatalogConfiguration($catalog)
    {
        parent::aCatalogConfiguration($catalog);

        $this->cleanProductCategoryAccesses();
        $this->cleanAssetCategoryAccesses();
    }

    /**
     * Remove "All" user group from all category accesses.
     * The "All" user group is added via the subscriber
     * \PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport\AddCategoryPermissionsSubscriber
     */
    protected function cleanProductCategoryAccesses()
    {
        $catAccessManager = $this->getContainer()->get('pimee_security.repository.category_access');
        $categories = $this->getContainer()->get('pim_catalog.repository.category')->findAll();
        $userGroups = $this->getContainer()->get('pim_user.repository.group')->findAllButDefault();

        foreach ($categories as $category) {
            $catAccessManager->revokeAccess($category, $userGroups);
        }
    }

    /**
     * Remove "All" user group from all category accesses.
     * The "All" user group is added via the subscriber
     * \PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport\AddCategoryPermissionsSubscriber
     */
    protected function cleanAssetCategoryAccesses()
    {
        $catAccessManager = $this->getContainer()->get('pimee_product_asset.repository.asset_category_access');
        $categories = $this->getContainer()->get('pimee_product_asset.repository.category')->findAll();
        $userGroups = $this->getContainer()->get('pim_user.repository.group')->findAllButDefault();

        foreach ($categories as $category) {
            $catAccessManager->revokeAccess($category, $userGroups);
        }
    }
}

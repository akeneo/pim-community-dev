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

        $this->cleanCategoryAccesses();
    }

    /**
     * Remove "All" user group from all category accesses.
     * The "All" user group is added via the subscriber
     * \PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport\AddCategoryPermissionsSubscriber
     */
    protected function cleanCategoryAccesses()
    {
        $catAccessManager = $this->getContainer()->get('pimee_security.repository.category_access');
        $categories = $this->getContainer()->get('pim_catalog.repository.category')->findAll();
        $userGroups = $this->getContainer()->get('pim_user.repository.group')->findAllButDefault();

        foreach ($categories as $category) {
            $catAccessManager->revokeAccess($category, $userGroups);
        }
    }

    /**
     *{@inheritdoc}
     *
     * TODO: Drop this once we'll have a proper import process for ProductAssets
     *
    protected function loadCatalog($files)
    {
        parent::loadCatalog($files);

        var_dump($files);
        die();

        $productAssetLoader = new ProductAssetLoader();
        $productAssetLoader->load($this->getEntityManager(), $this->getContainer()->getParameter('kernel.environment'));

    }*/
}

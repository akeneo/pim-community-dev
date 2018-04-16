<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\tests\Integration\Persistence;

use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\BuilderQueryTestCase;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

class PimEnterpriseBuilderQueryTestCase extends BuilderQueryTestCase
{
    /**
     * Creates an asset with data.
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return AssetInterface
     */
    protected function createAsset(array $data): AssetInterface
    {
        $asset = $this->get('pimee_product_asset.factory.asset')->create();

        $this->get('pimee_product_asset.updater.asset')->update($asset, $data);

        foreach ($asset->getReferences() as $reference) {
            $fileInfo = new \SplFileInfo($this->getFixturePath('ziggy.png'));
            $storedFile = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store(
                $fileInfo,
                FileStorage::ASSET_STORAGE_ALIAS
            );

            $reference->setFileInfo($storedFile);
            $this->get('pimee_product_asset.updater.files')->resetAllVariationsFiles($reference, true);
        }

        $errors = $this->get('validator')->validate($asset);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.asset')->save($asset);

        $this->get('pimee_product_asset.variations_collection_files_generator')->generate(
            $asset->getVariations(),
            true
        );

        return $asset;
    }

    /**
     * Creates an asset with data.
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return CategoryInterface
     */
    protected function createAssetCategory(array $data): CategoryInterface
    {
        $assetCategory = $this->get('pimee_product_asset.factory.category')->create();

        $this->get('pimee_product_asset.updater.category')->update($assetCategory, $data);

        $errors = $this->get('validator')->validate($assetCategory);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.category')->save($assetCategory);

        return $assetCategory;
    }
}

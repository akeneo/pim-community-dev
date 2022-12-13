<?php

declare(strict_types=1);

namespace Context;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Pim\Behat\Context\PimContext;

class EnterpriseAssetManagerContext extends PimContext
{
    /**
     * @Given /^an asset family simple link attribute$/
     */
    public function aAssetFamilySimpleLinkAttribute()
    {
        // Create the Asset family
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty(),
        );
        $this->getService('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')
            ->create($assetFamily);

        // Create a asset for this asset family
        $asset = Asset::create(
            AssetIdentifier::fromString('ikea'),
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('ikea'),
            ValueCollection::fromValues([])
        );
        $this->getService('akeneo_assetmanager.infrastructure.persistence.repository.asset')
            ->create($asset);

        $asset = Asset::create(
            AssetIdentifier::fromString('sony'),
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('sony'),
            ValueCollection::fromValues([])
        );
        $this->getService('akeneo_assetmanager.infrastructure.persistence.repository.asset')
            ->create($asset);


        // Create a new attribute linked to this asset family
        $attribute = $this->getService('pim_catalog.factory.attribute')
            ->createAttribute('akeneo_asset');
        $attribute->setCode('brand');
        $group = $this->getService('pim_catalog.repository.attribute_group')->findOneByIdentifier('other');
        $attribute->setGroup($group);
        $attribute->setUseableAsGridFilter(true);
        $attribute->setReferenceDataName('brand');
        $this->getService('pim_catalog.saver.attribute')
            ->save($attribute);
    }

    /**
     * @Given /^a product with a value for this asset family simple link attribute$/
     */
    public function aProductWithAValueForThisAssetFamilySimpleLinkAttribute()
    {
        $attribute = $this->getService('pim_catalog.repository.attribute')
            ->findOneByIdentifier('brand');

        // Create family
        $family = new Family();
        $family->setCode('chair');
        $family->setLocale('en_US')->setLabel('Chair');
        $family->addAttribute($attribute);

        $this->getService('pim_catalog.saver.family')
            ->save($family);

        // Create product
        $product = $this->getService('pim_catalog.builder.product')
            ->createProduct('tabouret', 'chair');

        $this->getService('pim_catalog.updater.product')
            ->update($product, [
                'values' => [
                    'brand' => [['locale' => null, 'scope' => null, 'data' => 'ikea']]
                ]
            ]);

        $this->getService('pim_catalog.saver.product')
            ->save($product);
    }

    /**
     * @Given an asset manager job import in CSV
     */
    public function anAssetManagerJobImportInCSV(): void
    {
        $jobInstance = new JobInstance('Akeneo CSV Connector', 'import', 'asset_manager_csv_asset_import');
        $jobInstance->setCode('test_csv');
        $jobInstance->setLabel('Asset Manager CSV import');
        $jobInstance->setRawParameters(['storage' => [
            'type' => 'local',
            'file_path' => 'test.csv',
        ]]);
        $this->getService('akeneo_batch.saver.job_instance')->save($jobInstance);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyIsLinkedToAtLeastOneProductAttributeTest extends SqlIntegrationTestCase
{
    private AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.asset_family_is_linked_to_at_least_one_product_attribute');
        $this->resetDB();
        $this->loadAssetFamily();
        $this->loadAttributeGroupAndAttribute();
    }

    /**
     * @test
     */
    public function it_tells_if_an_asset_family_is_linked_to_at_least_one_product_attribute()
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertTrue($isLinked);

        $identifier = AssetFamilyIdentifier::fromString('brand');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertFalse($isLinked);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamily(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function loadAttributeGroupAndAttribute(): void
    {
        $attributeGroup = new AttributeGroup();
        $this->get('pim_catalog.updater.attribute_group')
            ->update($attributeGroup, [
                'code' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attributeGroup);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute group "%s": %s',
                    $attributeGroup->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        $attributeAssetMultipleLink = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(AssetCollectionType::ASSET_COLLECTION);
        $this->get('pim_catalog.updater.attribute')
            ->update($attributeAssetMultipleLink, [
                'code' => 'main_designer',
                'reference_data_name' => 'designer',
                'group' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attributeAssetMultipleLink);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute "%s": %s',
                    $attributeAssetMultipleLink->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute')->save($attributeAssetMultipleLink);
    }
}

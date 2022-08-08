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
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetFamilyAttributeAsMainMediaTest extends SqlIntegrationTestCase
{
    private FindAssetFamilyAttributeAsMainMediaInterface $findAttributeAsMainMedia;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributeAsMainMedia = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_family_attribute_as_main_media');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_main_media_of_an_asset_family()
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        $expectedAttributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeAsMainMedia = $this->findAttributeAsMainMedia->find($assetFamilyIdentifier);

        $this->assertEquals($expectedAttributeAsMainMedia, $attributeAsMainMedia);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_main_media_if_the_asset_family_was_not_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown');
        $attributeAsMainMedia = $this->findAttributeAsMainMedia->find($assetFamilyIdentifier);

        $this->assertTrue($attributeAsMainMedia->isEmpty());
    }

    private function loadFixtures(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}

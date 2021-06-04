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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetFamilyAttributeAsLabelTest extends SqlIntegrationTestCase
{
    private FindAssetFamilyAttributeAsLabelInterface $findAttributeAsLabel;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributeAsLabel = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_family_attribute_as_label');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_label_of_an_asset_family()
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        $expectedAttributeAsLabel = $assetFamily->getAttributeAsLabelReference();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeAsLabel = $this->findAttributeAsLabel->find($assetFamilyIdentifier);

        $this->assertEquals($expectedAttributeAsLabel, $attributeAsLabel);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_label_if_the_asset_family_was_not_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown');
        $attributeAsLabel = $this->findAttributeAsLabel->find($assetFamilyIdentifier);

        $this->assertTrue($attributeAsLabel->isEmpty());
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->assetFamily('designer')
            ->load();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}

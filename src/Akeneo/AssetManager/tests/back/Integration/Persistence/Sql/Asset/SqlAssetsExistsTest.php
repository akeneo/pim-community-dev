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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlAssetsExists;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetsExistsTest extends SqlIntegrationTestCase
{
    private SqlAssetsExists $query;

    private string $assetIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.assets_exists');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_tells_if_there_are_corresponding_assets_identifiers()
    {
        $this->loadAssetFamilyDesigner();
        $this->loadAssetStarck();
        $existingAssetCodes = $this->query->withAssetFamilyAndCodes(
            AssetFamilyIdentifier::fromString('designer'),
            ['starck', 'coco', 'unknown']
        );
        $this->assertEquals(['coco', 'starck'], $existingAssetCodes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list_if_none_of_the_assets_exists()
    {
        $existingAssetCodes = $this->query->withAssetFamilyAndCodes(
            AssetFamilyIdentifier::fromString('designer'),
            ['unknown']
        );
        $this->assertEmpty($existingAssetCodes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyDesigner(): void
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

    public function loadAssetStarck(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetCode = AssetCode::fromString('starck');
        $this->assetIdentifier = AssetIdentifier::fromString('stark_designer_fingerprint');

        $assetRepository->create(
            Asset::create(
                $this->assetIdentifier,
                $assetFamilyIdentifier,
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                ])
            )
        );

        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('coco_designer_fingerprint'),
                $assetFamilyIdentifier,
                AssetCode::fromString('coco'),
                ValueCollection::fromValues([])
            )
        );
    }
}

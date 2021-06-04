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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
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
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyHasAssetsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyHasAssetsTest extends SqlIntegrationTestCase
{
    private AssetFamilyHasAssetsInterface $assetFamilyHasAssets;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyHasAssets = $this->get('akeneo_assetmanager.infrastructure.persistence.query.asset_family_has_assets');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_tells_if_an_asset_family_has_assets()
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $hasAssets = $this->assetFamilyHasAssets->hasAssets($identifier);
        $this->assertTrue($hasAssets);

        $identifier = AssetFamilyIdentifier::fromString('brand');
        $hasAssets = $this->assetFamilyHasAssets->hasAssets($identifier);
        $this->assertFalse($hasAssets);
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $this->loadDesigners();
        $this->loadBrands();
    }

    private function loadDesigners(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('stark');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                $assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode),
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
    }

    private function loadBrands(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }
}

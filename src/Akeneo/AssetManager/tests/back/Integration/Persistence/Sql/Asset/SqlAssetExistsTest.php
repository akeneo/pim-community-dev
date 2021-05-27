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
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetExistsTest extends SqlIntegrationTestCase
{
    private AssetExistsInterface $assetExists;

    private ?AssetFamilyIdentifier $assetFamilyIdentifier = null;

    private AssetIdentifier $assetIdentifier;

    private ?AssetCode $assetCode = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.asset_exists');
        $this->resetDB();
        $this->loadAssetFamilyDesigner();
        $this->loadAssetStarck();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_asset_identifier()
    {
        $this->assertTrue($this->assetExists->withIdentifier($this->assetIdentifier));
        $this->assertFalse($this->assetExists->withIdentifier(AssetIdentifier::fromString('unknown_asset_identifier')));
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_asset_code_for_asset_family()
    {
        $this->assertTrue($this->assetExists->withAssetFamilyAndCode($this->assetFamilyIdentifier, $this->assetCode));
        $this->assertFalse(
            $this->assetExists->withAssetFamilyAndCode($this->assetFamilyIdentifier, AssetCode::fromString('unknown'))
        );
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_code()
    {
        $this->assertTrue($this->assetExists->withCode($this->assetCode));
        $this->assertFalse($this->assetExists->withCode(AssetCode::fromString('unknown')));
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
        $this->assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier($this->assetFamilyIdentifier);

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->assetCode = AssetCode::fromString('starck');
        $this->assetIdentifier = AssetIdentifier::fromString('stark_designer_fingerprint');

        $assetRepository->create(
            Asset::create(
                $this->assetIdentifier,
                $this->assetFamilyIdentifier,
                $this->assetCode,
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
}

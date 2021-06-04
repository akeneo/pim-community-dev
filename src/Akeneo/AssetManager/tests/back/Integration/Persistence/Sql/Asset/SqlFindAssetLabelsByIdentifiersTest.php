<?php

declare(strict_types=1);

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
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByIdentifiersInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetLabelsByIdentifiersTest extends SqlIntegrationTestCase
{
    private FindAssetLabelsByIdentifiersInterface $query;

    private AssetIdentifier $starckIdentifier;

    private AssetIdentifier $dysonIdentifier;

    private AssetIdentifier $michaelIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_labels_by_identifiers');
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_finds_asset_labels_by_identifiers()
    {
        $result = $this->query->find([(string) $this->michaelIdentifier, (string) $this->dysonIdentifier]);
        $this->assertEquals([
            (string) $this->michaelIdentifier => [
                'labels' => ['fr_FR' => null, 'en_US' => null, 'de_DE' => null],
                'code' => 'michael'
            ],
            (string) $this->dysonIdentifier => [
                'labels' => ['fr_FR' => 'Dyson', 'en_US' => null, 'de_DE' => null],
                'code' => 'dyson'
            ],
        ], $result);

        $result = $this->query->find([(string) $this->starckIdentifier]);
        $this->assertEquals([
            (string) $this->starckIdentifier => [
                'labels' => ['fr_FR' => 'Philippe Starck', 'en_US' => 'Philippe Starck', 'de_DE' => null],
                'code' => 'starck'
            ],
        ], $result);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
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

        // Starck asset
        $starckCode = AssetCode::fromString('starck');
        $assetIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $starckCode);
        $this->starckIdentifier = $assetIdentifier;
        $labelValueFR = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Philippe Starck')
        );
        $labelValueUS = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck')
        );
        $assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValueFR, $labelValueUS])
            )
        );

        // Dyson asset
        $dysonCode = AssetCode::fromString('dyson');
        $assetIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $dysonCode);
        $this->dysonIdentifier = $assetIdentifier;
        $labelValueFR = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson')
        );
        $assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $dysonCode,
                ValueCollection::fromValues([$labelValueFR])
            )
        );

        // Michael asset
        $michaelCode = AssetCode::fromString('michael');
        $assetIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $michaelCode);
        $this->michaelIdentifier = $assetIdentifier;
        $assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $michaelCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

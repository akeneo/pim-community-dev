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

namespace Akeneo\AssetManager\Integration\PublicApi\Onboarder;

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
use Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder\AssetLabels;
use Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder\FindAllAssetLabels;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindAllAssetLabelsTest extends SqlIntegrationTestCase
{
    /** @var FindAllAssetLabels*/
    private $query;

    /** @var AssetIdentifier */
    private $starckIdentifier;

    /** @var AssetIdentifier */
    private $dysonIdentifier;

    /** @var AssetIdentifier */
    private $michaelIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.onboarder.find_all_asset_labels');
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_finds_all_asset_labels()
    {
        $assets = $this->query->find();
        $assets = iterator_to_array($assets);
        Assert::assertContainsEquals(new AssetLabels(
            (string) $this->michaelIdentifier,
            ['fr_FR' => null, 'en_US' => null, 'de_DE' => null],
            'michael',
            'designer'
        ), $assets);
        Assert::assertContainsEquals(new AssetLabels(
           (string) $this->starckIdentifier,
           ['fr_FR' => 'Philippe Starck', 'en_US' => 'Philippe Starck US', 'de_DE' => null],
           'starck',
           'designer'
        ), $assets);
        Assert::assertContainsEquals(new AssetLabels(
           (string) $this->dysonIdentifier,
           ['fr_FR' => 'Dyson', 'en_US' => null, 'de_DE' => null],
           'dyson',
           'designer'
        ), $assets);
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
            TextData::fromString('Philippe Starck US')
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

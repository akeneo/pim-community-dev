<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAssetFamilyAttributeAsLabel;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAssetLabelsByCodes;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindAssetLabelsByCodesTest extends TestCase
{
    private InMemoryFindAssetLabelsByCodes $findAssetLabelsByCodesQuery;

    private InMemoryAssetRepository $assetRepository;

    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetRepository = new InMemoryAssetRepository(new EventDispatcher());
        $this->assetFamilyRepository = new InMemoryAssetFamilyRepository(new EventDispatcher());

        $this->findAssetLabelsByCodesQuery = new InMemoryFindAssetLabelsByCodes(
            $this->assetRepository,
            new InMemoryFindAssetFamilyAttributeAsLabel(
                $this->assetFamilyRepository
            )
        );

        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_finds_labels_for_given_asset_codes()
    {
        $labels = $this->findAssetLabelsByCodesQuery->find(
            AssetFamilyIdentifier::fromString('designer'),
            ['starck', 'dyson', 'michael']
        );

        $this->assertNotEmpty($labels);
        $this->assertContainsOnlyInstancesOf(LabelCollection::class, $labels);

        $this->assertEquals(
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck', 'en_US' => 'Philippe Starck']),
            $labels['starck']
        );

        $this->assertEquals(
            LabelCollection::fromArray(['fr_FR' => 'Dyson']),
            $labels['dyson']
        );

        $this->assertEquals(
            LabelCollection::fromArray([]),
            $labels['michael']
        );
    }

    private function loadAssetFamilyAndAssets(): void
    {
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
        $this->assetFamilyRepository->create($assetFamily);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetFamily->updateAttributeAsLabelReference(AttributeAsLabelReference::createFromNormalized('label'));

        // Starck asset
        $starckCode = AssetCode::fromString('starck');
        $assetIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $starckCode);
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
        $this->assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValueFR, $labelValueUS])
            )
        );

        // Dyson asset
        $dysonCode = AssetCode::fromString('dyson');
        $assetIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $dysonCode);
        $labelValueFR = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson')
        );
        $this->assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $dysonCode,
                ValueCollection::fromValues([$labelValueFR])
            )
        );

        // Michael asset
        $michaelCode = AssetCode::fromString('michael');
        $assetIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $michaelCode);
        $this->assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $michaelCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

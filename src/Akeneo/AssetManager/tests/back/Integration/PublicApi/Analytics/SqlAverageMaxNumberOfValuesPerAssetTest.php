<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\PublicApi\Analytics;

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
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfValuesPerAsset;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfValuesPerAssetTest extends SqlIntegrationTestCase
{
    private AssetRepositoryInterface $assetRepository;

    private SqlAverageMaxNumberOfValuesPerAsset $averageMaxNumberOfValuesPerAssets;

    private AttributeRepositoryInterface $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->averageMaxNumberOfValuesPerAssets = $this->get('akeneo_assetmanager.infrastructure.persistence.query.analytics.average_max_number_of_values_per_asset');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_values_per_asset()
    {
        $this->loadAssetWithNumberOfValues(2);
        $this->loadAssetWithNumberOfValues(4);

        $volume = $this->averageMaxNumberOfValuesPerAssets->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('3', $volume->getAverageVolume());
    }

    private function loadAssetWithNumberOfValues(int $numberOfValuesForAsset): void
    {
        $assetFamilyIdentifier = $this->createAssetFamily();
        $attributes = $this->createAttributes($numberOfValuesForAsset, $assetFamilyIdentifier);

        $this->createAssetWithOneValueForEachAttribute($assetFamilyIdentifier, $attributes);
    }

    private function createAssetFamily(): AssetFamilyIdentifier
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($this->randomString());
        $assetFamilyRepository->create(AssetFamily::create(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        ));

        return $assetFamilyIdentifier;
    }

    /**
     * @return mixed
     *
     */
    private function randomString(): string
    {
        return str_replace('-', '_', Uuid::uuid4()->toString());
    }

    /**
     * @param int $numberOfValuesForAsset
     * @param     $assetFamilyIdentifier
     *
     * @return array
     *
     */
    private function createAttributes(int $numberOfValuesForAsset, $assetFamilyIdentifier): array
    {
        return array_map(
            function (int $index) use ($assetFamilyIdentifier) {
                $identifier = sprintf('%s%d', $assetFamilyIdentifier->normalize(), $index);
                $attribute = TextAttribute::createText(
                    AttributeIdentifier::fromString($identifier),
                    $assetFamilyIdentifier,
                    AttributeCode::fromString($identifier),
                    LabelCollection::fromArray([]),
                    AttributeOrder::fromInteger($index + 2), // Labels and Image are created by default
                    AttributeIsRequired::fromBoolean(false),
                    AttributeIsReadOnly::fromBoolean(false),
                    AttributeValuePerChannel::fromBoolean(false),
                    AttributeValuePerLocale::fromBoolean(false),
                    AttributeMaxLength::fromInteger(255),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                );
                $this->attributeRepository->create($attribute);

                return $attribute;
            },
            range(1, $numberOfValuesForAsset)
        );
    }

    /**
     * @param AttributeInterface[] $attributes
     */
    private function createAssetWithOneValueForEachAttribute(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $attributes
    ): void {
        $valueCollection = $this->generateValues($attributes);
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($this->randomString()),
                $assetFamilyIdentifier,
                AssetCode::fromString($this->randomString()),
                $valueCollection
            )
        );
    }

    /**
     * @param AttributeInterface[] $attributes
     */
    private function generateValues(array $attributes): ValueCollection
    {
        return ValueCollection::fromValues(
            array_map(fn(AbstractAttribute $attribute) => Value::create(
                $attribute->getIdentifier(),
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                TextData::fromString('Some text data')
            ), $attributes)
        );
    }
}

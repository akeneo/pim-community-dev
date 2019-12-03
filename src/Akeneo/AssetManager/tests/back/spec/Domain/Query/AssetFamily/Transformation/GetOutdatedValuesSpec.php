<?php

namespace spec\Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetOutdatedValues;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;

class GetOutdatedValuesSpec extends ObjectBehavior
{
    private const SOURCE_ATTRIBUTE_CODE = 'source';
    private const TARGET_ATTRIBUTE_CODE = 'target';
    private const ASSET_FAMILY_CODE = 'my_asset_family';
    private const FINGERPRINT = 'fingerprint';

    function let(
        GetAttributeIdentifierInterface $getAttributeIdentifier
    ) {
        $this->beConstructedWith($getAttributeIdentifier);

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE),
            AttributeCode::fromString(self::SOURCE_ATTRIBUTE_CODE)
        )->willReturn(AttributeIdentifier::fromString(join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT])));

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE),
            AttributeCode::fromString(self::TARGET_ATTRIBUTE_CODE)
        )->willReturn(AttributeIdentifier::fromString(join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT])));

        $this->shouldHaveType(GetOutdatedValues::class);
    }

    function it_returns_nothing_if_there_is_no_source_value(
        Asset $asset,
        Value $targetValue
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn(null);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([]));
    }

    function it_returns_target_if_there_is_no_target_value(
        Asset $asset,
        Value $sourceValue,
        FileData $sourceData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn(null);

        $sourceValue->getData()->willReturn($sourceData);

        $targetValue = Value::create(
            AttributeIdentifier::fromString(join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT])),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            EmptyData::create()
        );

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([$targetValue]));
    }

    function it_returns_nothing_if_source_value_is_not_a_file(
        Asset $asset,
        Value $sourceValue,
        Value $targetValue,
        NumberData $wrongData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $sourceValue->getData()->willReturn($wrongData);

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([]));
    }

    function it_returns_nothing_if_target_value_is_not_a_file(
        Asset $asset,
        Value $sourceValue,
        Value $targetValue,
        FileData $sourceData,
        NumberData $wrongData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $sourceValue->getData()->willReturn($sourceData);
        $targetValue->getData()->willReturn($wrongData);

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([]));
    }

    function it_returns_target_if_source_has_no_timestamp(
        Asset $asset,
        Value $sourceValue,
        Value $targetValue,
        FileData $sourceData,
        FileData $targetData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $sourceValue->getData()->willReturn($sourceData);
        $sourceData->getUpdatedAt()->willReturn(null);
        $targetValue->getData()->willReturn($targetData);
        $targetValue->getValueKey()->willReturn(ValueKey::createFromNormalized('targetKey'));

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([$targetValue->getWrappedObject()]));
    }

    function it_returns_target_if_target_has_no_timestamp(
        Asset $asset,
        Value $sourceValue,
        Value $targetValue,
        FileData $sourceData,
        FileData $targetData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $sourceValue->getData()->willReturn($sourceData);
        $sourceData->getUpdatedAt()->willReturn(new \DateTime());

        $targetValue->getData()->willReturn($targetData);
        $targetData->getUpdatedAt()->willReturn(null);
        $targetValue->getValueKey()->willReturn(ValueKey::createFromNormalized('targetKey'));

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([$targetValue->getWrappedObject()]));
    }

    function it_returns_nothing_if_source_was_updated_before_target(
        Asset $asset,
        Value $sourceValue,
        Value $targetValue,
        FileData $sourceData,
        FileData $targetData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $dateTimeSource = new \DateTime('2019-11-25');
        $dateTimeTarget = new \DateTime('2019-11-26');

        $sourceValue->getData()->willReturn($sourceData);
        $sourceData->getUpdatedAt()->willReturn($dateTimeSource);
        $targetValue->getData()->willReturn($targetData);
        $targetData->getUpdatedAt()->willReturn($dateTimeTarget);

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([]));
    }

    function it_returns_target_if_source_was_updated_after_target(
        Asset $asset,
        Value $sourceValue,
        Value $targetValue,
        FileData $sourceData,
        FileData $targetData
    ) {
        $transformationCollection = $this->getStandardTransformationCollection();
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE));
        $sourceKey = join('_', [self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::SOURCE_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $targetKey = join('_', [self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::TARGET_ATTRIBUTE_CODE, self::ASSET_FAMILY_CODE, self::FINGERPRINT]);
        $asset->findValue(ValueKey::createFromNormalized($sourceKey))->willReturn($sourceValue);
        $asset->findValue(ValueKey::createFromNormalized($targetKey))->willReturn($targetValue);

        $dateTimeSource = new \DateTime('2019-11-25');
        $dateTimeTarget = new \DateTime('2019-11-24');

        $sourceValue->getData()->willReturn($sourceData);
        $sourceData->getUpdatedAt()->willReturn($dateTimeSource);
        $targetValue->getData()->willReturn($targetData);
        $targetData->getUpdatedAt()->willReturn($dateTimeTarget);
        $targetValue->getValueKey()->willReturn(ValueKey::createFromNormalized('targetKey'));

        $this->fromAsset($asset, $transformationCollection)->shouldBeLike(ValueCollection::fromValues([$targetValue->getWrappedObject()]));
    }

    private function getStandardTransformationCollection(): TransformationCollection
    {
        $transformationCollection = TransformationCollection::create([
            Transformation::create(
                Source::create(
                    $this->createMediaFileAttribute(self::SOURCE_ATTRIBUTE_CODE),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
                ),
                Target::create(
                    $this->createMediaFileAttribute('target'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
                ),
                OperationCollection::create([]),
                '',
                '_2'
            )
        ]);

        return $transformationCollection;
    }

    private function createMediaFileAttribute(string $attributeCode): MediaFileAttribute
    {
        return MediaFileAttribute::create(
            AttributeIdentifier::fromString($attributeCode . '_' . self::ASSET_FAMILY_CODE . '_'. self::FINGERPRINT),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_CODE),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED)
        );
    }
}

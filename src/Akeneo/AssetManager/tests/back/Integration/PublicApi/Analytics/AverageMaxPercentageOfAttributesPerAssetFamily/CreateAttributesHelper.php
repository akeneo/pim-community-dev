<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerAssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
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
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributesHelper
{
    private AttributeRepositoryInterface $attributeRepository;

    private int $attributeOrder = 2;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function loadLocalizableOnlyAttributesForAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        int $numberOfAttributesToCreate
    ): void {
        // By default the label is localizable only
        $numberOfAttributesToCreate -= 1;
        $this->createAttributesWith($assetFamilyIdentifier, $numberOfAttributesToCreate, false, true);
    }

    private function createAttributesWith(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        int $numberOfAttributesToCreate,
        bool $hasOneValuePerChannel,
        bool $hasOneValuePerLocale
    ): void {
        // By default, there are already 2 attributes created for each asset family
        for ($i = 0; $i < $numberOfAttributesToCreate; $i++) {
            $this->attributeOrder++;
            $identifier = sprintf('%s_%d', $assetFamilyIdentifier->normalize(), $this->attributeOrder);
            $this->attributeRepository->create(
                TextAttribute::createText(
                    AttributeIdentifier::fromString($identifier),
                    $assetFamilyIdentifier,
                    AttributeCode::fromString($identifier),
                    LabelCollection::fromArray(['en_US' => 'Name']),
                    AttributeOrder::fromInteger($this->attributeOrder),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsReadOnly::fromBoolean(false),
                    AttributeValuePerChannel::fromBoolean($hasOneValuePerChannel),
                    AttributeValuePerLocale::fromBoolean($hasOneValuePerLocale),
                    AttributeMaxLength::fromInteger(155),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                )
            );
        }
    }

    public function loadLocalizableAndScopableAttributesForAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        int $numberOfAttributesToCreate
    ): void {
        $this->createAttributesWith($assetFamilyIdentifier, $numberOfAttributesToCreate, true, true);
    }

    public function loadScopableOnlyAttributesForAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        int $numberOfAttributesToCreate
    ): void {
        $this->createAttributesWith($assetFamilyIdentifier, $numberOfAttributesToCreate, true, false);
    }

    public function loadNotLocalizableNotScopableAttributesForAssetFamily(
        $assetFamilyIdentifier,
        int $numberOfAttributesToCreate
    ) {
        // By default, the image is not localizable nor scopable
        $numberOfAttributesToCreate -= 1;
        $this->createAttributesWith($assetFamilyIdentifier, $numberOfAttributesToCreate, false, false);
    }
}

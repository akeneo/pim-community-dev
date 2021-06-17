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

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
abstract class AbstractAttribute
{
    protected AttributeIdentifier $identifier;

    protected AssetFamilyIdentifier $assetFamilyIdentifier;

    protected AttributeCode $code;

    protected LabelCollection $labelCollection;

    protected AttributeOrder $order;

    protected AttributeIsRequired $isRequired;

    protected AttributeIsReadOnly $isReadOnly;

    protected AttributeValuePerChannel $valuePerChannel;

    protected AttributeValuePerLocale $valuePerLocale;

    protected function __construct(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) {
        $this->identifier = $identifier;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->order = $order;
        $this->isRequired = $isRequired;
        $this->isReadOnly = $isReadOnly;
        $this->valuePerChannel = $valuePerChannel;
        $this->valuePerLocale = $valuePerLocale;
    }

    public function getIdentifier(): AttributeIdentifier
    {
        return $this->identifier;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function equals(AbstractAttribute $attribute): bool
    {
        return $this->identifier->equals($attribute->identifier) &&
            $this->assetFamilyIdentifier->equals($attribute->assetFamilyIdentifier);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labelCollection->getLabel($localeCode);
    }

    public function getLabelCodes(): array
    {
        return $this->labelCollection->getLocaleCodes();
    }

    public function hasOrder(AttributeOrder $order): bool
    {
        return $this->order->intValue() === $order->intValue();
    }

    public function getOrder(): AttributeOrder
    {
        return $this->order;
    }

    public function updateLabels(LabelCollection $labelCollection): void
    {
        $labels = $this->labelCollection->normalize();
        $updatedLabels = $labelCollection->normalize();
        $this->labelCollection = LabelCollection::fromArray(array_merge($labels, $updatedLabels));
    }

    public function setIsRequired(AttributeIsRequired $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    public function setIsReadOnly(AttributeIsReadOnly $isReadOnly): void
    {
        $this->isReadOnly = $isReadOnly;
    }

    public function normalize(): array
    {
        return [
            'identifier' => (string) $this->identifier,
            'asset_family_identifier' => (string) $this->assetFamilyIdentifier,
            'code' => (string) $this->code,
            'labels' => $this->labelCollection->normalize(),
            'order' => $this->order->intValue(),
            'is_required' => $this->isRequired->normalize(),
            'is_read_only' => $this->isReadOnly->normalize(),
            'value_per_channel' => $this->valuePerChannel->normalize(),
            'value_per_locale' => $this->valuePerLocale->normalize(),
            'type' => $this->getType(),
        ];
    }

    public function hasValuePerChannel(): bool
    {
        return $this->valuePerChannel->isTrue();
    }

    public function hasValuePerLocale(): bool
    {
        return $this->valuePerLocale->isTrue();
    }

    abstract public function getType(): string;
}

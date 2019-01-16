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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractAttribute
{
    /** @var AttributeIdentifier */
    protected $identifier;

    /** @var ReferenceEntity */
    protected $referenceEntityIdentifier;

    /** @var AttributeCode */
    protected $code;

    /** @var LabelCollection */
    protected $labelCollection;

    /** @var AttributeOrder */
    protected $order;

    /** @var AttributeIsRequired */
    protected $isRequired;

    /** @var AttributeValuePerChannel */
    protected $valuePerChannel;

    /** @var AttributeValuePerLocale */
    protected $valuePerLocale;

    protected function __construct(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) {
        $this->identifier = $identifier;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->order = $order;
        $this->isRequired = $isRequired;
        $this->valuePerChannel = $valuePerChannel;
        $this->valuePerLocale = $valuePerLocale;
    }

    public function getIdentifier(): AttributeIdentifier
    {
        return $this->identifier;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function equals(AbstractAttribute $attribute): bool
    {
        return $this->identifier->equals($attribute->identifier) &&
            $this->referenceEntityIdentifier->equals($attribute->referenceEntityIdentifier);
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

    public function normalize(): array
    {
        return [
            'identifier' => (string) $this->identifier,
            'reference_entity_identifier' => (string) $this->referenceEntityIdentifier,
            'code' => (string) $this->code,
            'labels' => $this->labelCollection->normalize(),
            'order' => $this->order->intValue(),
            'is_required' => $this->isRequired->normalize(),
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

    abstract protected function getType(): string;
}

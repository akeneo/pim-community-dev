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

namespace Akeneo\Category\Domain\Model\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIdentifier;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeValuePerChannel;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeValuePerLocale;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * @api
 */
abstract class AbstractAttribute
{
    protected function __construct(
        protected AttributeIdentifier $identifier,
        protected TemplateIdentifier $templateIdentifier,
        protected AttributeCode $code,
        protected LabelCollection $labelCollection,
        protected AttributeOrder $order,
        protected AttributeIsRequired $isRequired,
        protected AttributeValuePerChannel $valuePerChannel,
        protected AttributeValuePerLocale $valuePerLocale
    ) {
    }

    public function getIdentifier(): AttributeIdentifier
    {
        return $this->identifier;
    }

    public function getTemplateIdentifier(): TemplateIdentifier
    {
        return $this->templateIdentifier;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function equals(AbstractAttribute $attribute): bool
    {
        return $this->identifier->equals($attribute->identifier) &&
            $this->templateIdentifier->equals($attribute->templateIdentifier);
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
            'reference_entity_identifier' => (string) $this->templateIdentifier,
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

    abstract public function getType(): string;
}

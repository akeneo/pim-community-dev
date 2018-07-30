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

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractAttribute
{
    /** @var AttributeIdentifier */
    private $identifier;

    /** @var EnrichedEntity */
    private $enrichedEntityIdentifier;

    /** @var AttributeCode */
    private $code;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var AttributeOrder */
    private $order;

    /** @var AttributeRequired */
    private $required;

    /** @var AttributeValuePerChannel */
    private $valuePerChannel;

    /** @var AttributeValuePerLocale */
    private $valuePerLocale;

    protected function __construct(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeRequired $required,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) {
        Assert::eq($identifier->getIdentifier(), (string) $code, sprintf(
                'The identifier and attribute code should be the same, "%s" and "%s" given.',
                $identifier->getIdentifier(),
                (string) $code
            )
        );
        Assert::eq($identifier->getEnrichedEntityIdentifier(), (string) $enrichedEntityIdentifier, sprintf(
                'The identifier and enriched entity identifier should be related, "%s" and "%s" given.',
                $identifier->getEnrichedEntityIdentifier(),
                (string) $enrichedEntityIdentifier
            )
        );

        $this->identifier = $identifier;
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->order = $order;
        $this->required = $required;
        $this->valuePerChannel = $valuePerChannel;
        $this->valuePerLocale = $valuePerLocale;
    }

    public function getIdentifier(): AttributeIdentifier
    {
        return $this->identifier;
    }

    public function getEnrichedEntityIdentifier(): EnrichedEntityIdentifier
    {
        return $this->enrichedEntityIdentifier;
    }

    public function equals(AbstractAttribute $attribute): bool
    {
        return $this->identifier->equals($attribute->identifier) &&
            $this->enrichedEntityIdentifier->equals($attribute->enrichedEntityIdentifier);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labelCollection->getLabel($localeCode);
    }

    public function getLabelCodes(): array
    {
        return $this->labelCollection->getLocaleCodes();
    }

    public function updateLabels(LabelCollection $labelCollection): void
    {
        $this->labelCollection = $labelCollection;
    }

    public function hasOrder(AttributeOrder $order): bool
    {
        return $this->order->intValue() === $order->intValue();
    }

    abstract protected function getType(): string;

    public function normalize(): array
    {
        return [
            'identifier' => [
                'enriched_entity_identifier' => $this->identifier->getEnrichedEntityIdentifier(),
                'identifier' => $this->identifier->getIdentifier()
            ],
            'enriched_entity_identifier' => (string) $this->enrichedEntityIdentifier,
            'code' => (string) $this->code,
            'labels' => $this->labelCollection->normalize(),
            'order' => $this->order->intValue(),
            'required' => $this->required->isYes(),
            'value_per_channel' => $this->valuePerChannel->isYes(),
            'value_per_locale' => $this->valuePerLocale->isYes(),
            'type' => $this->getType(),
        ];
    }
}

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

namespace Akeneo\EnrichedEntity\Domain\Model\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class Record
{
    /** @var RecordIdentifier */
    private $identifier;

    /** @var RecordCode */
    private $code;

    /** @var EnrichedEntity */
    private $enrichedEntityIdentifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var ValueCollection */
    private $valueCollection;

    private function __construct(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection,
        ValueCollection $valueCollection
    ) {
        $this->identifier = $identifier;
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->valueCollection = $valueCollection;
    }

    public static function create(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        array $rawLabelCollection, // TODO: receive LabelCollection instead
        ValueCollection $valueCollection
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self($identifier, $enrichedEntityIdentifier, $code, $labelCollection, $valueCollection);
    }

    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
    }

    public function getCode(): RecordCode
    {
        return $this->code;
    }

    public function getEnrichedEntityIdentifier(): EnrichedEntityIdentifier
    {
        return $this->enrichedEntityIdentifier;
    }

    public function equals(Record $record): bool
    {
        return $this->identifier->equals($record->identifier);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labelCollection->getLabel($localeCode);
    }

    public function getLabelCodes(): array
    {
        return $this->labelCollection->getLocaleCodes();
    }

    public function setLabels(LabelCollection $labelCollection): void
    {
        $this->labelCollection = $labelCollection;
    }

    public function setValues(ValueCollection $valueCollection): void
    {
        $this->valueCollection = $valueCollection;
    }

    public function getValues(): ValueCollection
    {
        return $this->valueCollection;
    }

    public function normalize(): array
    {
        return [
            'identifier' => $this->identifier->normalize(),
            'code' => $this->code->normalize(),
            'enrichedEntityIdentifier' => $this->enrichedEntityIdentifier->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'values' => $this->valueCollection->normalize()
        ];
    }

    public function setValue(Value $value): void
    {
        $this->valueCollection = $this->valueCollection->setValue($value);
    }
}

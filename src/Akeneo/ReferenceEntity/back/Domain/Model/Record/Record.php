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

namespace Akeneo\ReferenceEntity\Domain\Model\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;

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

    /** @var ReferenceEntity */
    private $referenceEntityIdentifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image */
    private $image;

    /** @var ValueCollection */
    private $valueCollection;

    private function __construct(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection,
        Image $image,
        ValueCollection $valueCollection
    ) {
        $this->identifier = $identifier;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->image = $image;
        $this->valueCollection = $valueCollection;
    }

    public static function create(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        array $rawLabelCollection, // TODO: receive LabelCollection instead
        Image $image,
        ValueCollection $valueCollection
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self($identifier, $referenceEntityIdentifier, $code, $labelCollection, $image, $valueCollection);
    }

    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
    }

    public function getCode(): RecordCode
    {
        return $this->code;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
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

    public function getImage(): Image
    {
        return $this->image;
    }

    public function updateImage(Image $image): void
    {
        $this->image = $image;
    }

    public function getValues(): ValueCollection
    {
        return $this->valueCollection;
    }

    public function setValue(Value $value): void
    {
        $this->valueCollection = $this->valueCollection->setValue($value);
    }

    public function findValue(ValueKey $valueKey): ?Value
    {
        return $this->valueCollection->findValue($valueKey);
    }

    public function normalize(): array
    {
        return [
            'identifier' => $this->identifier->normalize(),
            'code' => $this->code->normalize(),
            'referenceEntityIdentifier' => $this->referenceEntityIdentifier->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'values' => $this->valueCollection->normalize(),
            'image' => $this->image->normalize(),
        ];
    }

    public function filterValues(\Closure $closure): ValueCollection
    {
        return $this->valueCollection->filter($closure);
    }
}

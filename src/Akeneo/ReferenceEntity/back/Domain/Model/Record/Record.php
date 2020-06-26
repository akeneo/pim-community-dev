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

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    /** @var ValueCollection */
    private $valueCollection;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var \DateTimeImmutable */
    private $updatedAt;

    private function __construct(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        ValueCollection $valueCollection,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->identifier = $identifier;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->code = $code;
        $this->valueCollection = $valueCollection;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        ValueCollection $valueCollection
    ): self {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $valueCollection,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }

    public static function fromState(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        ValueCollection $valueCollection,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        return new self($identifier, $referenceEntityIdentifier, $code, $valueCollection, $createdAt, $updatedAt);
    }

    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
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

    public function getValues(): ValueCollection
    {
        return $this->valueCollection;
    }

    public function setValue(Value $value): void
    {
        $this->valueCollection = $this->valueCollection->setValue($value);
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
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
            'values' => $this->valueCollection->normalize(),
        ];
    }

    public function filterValues(\Closure $closure): ValueCollection
    {
        return $this->valueCollection->filter($closure);
    }
}

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
use Webmozart\Assert\Assert;

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

    private function __construct(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection
    ) {
        $this->identifier = $identifier;
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->labelCollection = $labelCollection;
        $this->code = $code;
    }

    public static function create(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        array $rawLabelCollection
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self($identifier, $enrichedEntityIdentifier, $code, $labelCollection);
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
        return $this->identifier->equals($record->identifier) &&
            $this->enrichedEntityIdentifier->equals($record->enrichedEntityIdentifier);
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
}

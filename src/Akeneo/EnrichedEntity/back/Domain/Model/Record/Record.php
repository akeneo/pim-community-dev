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

namespace Akeneo\EnrichedEntity\back\Domain\Model\Record;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class Record
{
    /** @var RecordIdentifier */
    private $identifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var EnrichedEntity */
    private $enrichedEntityIdentifier;

    private function __construct(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        LabelCollection $labelCollection
    ) {
        $this->identifier = $identifier;
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->labelCollection = $labelCollection;
    }

    public static function create(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        array $rawLabelCollection
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self($identifier, $enrichedEntityIdentifier, $labelCollection);
    }

    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
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

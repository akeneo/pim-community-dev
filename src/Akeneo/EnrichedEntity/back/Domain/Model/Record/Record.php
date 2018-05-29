<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Model\Record;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        LabelCollection $labelCollection
    ): self {
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

    public function getTranslation(string $localeCode): ?string
    {
        return $this->labelCollection->getLabel($localeCode);
    }
}

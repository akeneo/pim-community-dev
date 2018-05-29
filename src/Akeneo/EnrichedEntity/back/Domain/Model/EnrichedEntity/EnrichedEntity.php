<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;

class EnrichedEntity
{
    /** @var EnrichedEntityIdentifier */
    private $identifier;

    /** @var LabelCollection */
    private $labelCollection;

    private function __construct(EnrichedEntityIdentifier $identifier, LabelCollection $labelCollection)
    {
        $this->identifier = $identifier;
        $this->labelCollection = $labelCollection;
    }

    public static function define(EnrichedEntityIdentifier $identifier, LabelCollection $labelCollection): self
    {
        return new self($identifier, $labelCollection);
    }

    public function getIdentifier(): EnrichedEntityIdentifier
    {
        return $this->identifier;
    }

    public function equals(EnrichedEntity $enrichedEntity): bool
    {
        return $this->identifier->equals($enrichedEntity->identifier);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labelCollection->getLabel($localeCode);
    }

    public function getLabelCodes(): array
    {
        return $this->labelCollection->getLocaleCodes();
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public static function create(EnrichedEntityIdentifier $identifier, array $rawLabelCollection): self
    {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

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

    public function updateLabels(LabelCollection $labelCollection): void
    {
        $this->labelCollection = $labelCollection;
    }
}

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

namespace Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntity
{
    /** @var EnrichedEntityIdentifier */
    private $identifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image */
    private $image;

    private function __construct(
        EnrichedEntityIdentifier $identifier,
        LabelCollection $labelCollection
    ) {
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

    public function getImage(): ?string
    {
        if (null === $this->image) {
            return null;
        }

        return $this->image->getKey();
    }

    public function updateLabels(LabelCollection $labelCollection): void
    {
        $this->labelCollection = $labelCollection;
    }

    public function updateImage(Image $image): void
    {
        $this->image = $image;
    }
}

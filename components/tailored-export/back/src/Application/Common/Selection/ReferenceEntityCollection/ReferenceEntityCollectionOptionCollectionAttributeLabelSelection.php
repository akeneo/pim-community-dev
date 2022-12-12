<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection;

final class ReferenceEntityCollectionOptionCollectionAttributeLabelSelection implements ReferenceEntityCollectionOptionCollectionAttributeSelectionInterface
{
    public const TYPE = 'label';

    public function __construct(
        private string $separator,
        private string $referenceEntityCode,
        private string $referenceEntityAttributeIdentifier,
        private string $optionSeparator,
        private string $labelLocale,
        private ?string $channel,
        private ?string $locale,
    ) {
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getOptionSeparator(): string
    {
        return $this->optionSeparator;
    }

    public function getLabelLocale(): string
    {
        return $this->labelLocale;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getReferenceEntityCode(): string
    {
        return $this->referenceEntityCode;
    }

    public function getReferenceEntityAttributeIdentifier(): string
    {
        return $this->referenceEntityAttributeIdentifier;
    }
}

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

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity;

final class ReferenceEntityTextAttributeSelection implements ReferenceEntityAttributeSelectionInterface
{
    public const TYPE = 'text';

    public function __construct(
        private string $referenceEntityCode,
        private string $referenceEntityAttributeCode,
        private ?string $channel,
        private ?string $locale,
    ) {
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

    public function getReferenceEntityAttributeCode(): string
    {
        return $this->referenceEntityAttributeCode;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection;

final class ReferenceEntityCollectionLabelSelection implements ReferenceEntityCollectionSelectionInterface
{
    public const TYPE = 'label';

    public function __construct(
        private string $separator,
        private string $locale,
        private string $referenceEntityCode,
    ) {
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getReferenceEntityCode(): string
    {
        return $this->referenceEntityCode;
    }
}

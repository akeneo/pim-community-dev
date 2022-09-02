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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\FormatApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\TailoredExport\Application\Common\Format\FormatInterface;

class FormatApplier
{
    public function __construct(
        private ConcatFormatApplier $concatFormatApplier,
    ) {
    }

    public function applyFormat(
        FormatInterface $format,
        array $mappedValues,
    ): string {
        if (!$format instanceof ConcatFormat) {
            throw new \InvalidArgumentException('Unsupported format');
        }

        return $this->concatFormatApplier->applyFormat($format, $mappedValues);
    }
}

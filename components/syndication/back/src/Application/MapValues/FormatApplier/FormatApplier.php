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

namespace Akeneo\Platform\Syndication\Application\MapValues\FormatApplier;

use Akeneo\Platform\Syndication\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\Syndication\Application\Common\Format\FormatInterface;
use Akeneo\Platform\Syndication\Application\Common\Format\NoneFormat;

class FormatApplier
{
    private ConcatFormatApplier $concatFormatApplier;
    private NoneFormatApplier $noneApplier;

    public function __construct(ConcatFormatApplier $concatFormatApplier, NoneFormatApplier $noneApplier)
    {
        $this->concatFormatApplier = $concatFormatApplier;
        $this->noneApplier = $noneApplier;
    }

    public function applyFormat(
        FormatInterface $format,
        array $mappedValues
    ) {
        if ($format instanceof ConcatFormat) {
            return $this->concatFormatApplier->applyFormat($format, $mappedValues);
        }

        if ($format instanceof NoneFormat) {
            return $this->noneApplier->applyFormat($format, $mappedValues);
        }

        throw new \InvalidArgumentException('Unsupported format');
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Result;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellGlobalOffsetCalculator
{
    public function compute(string $source, int $lineNumber, int $offsetFromLine, string $separator = "\n"): int
    {
        if ($lineNumber < 0 || $offsetFromLine < 0) {
            return 0;
        }

        $lines = explode($separator, $source);
        if ($lineNumber > count($lines)) {
            return mb_strlen($source);
        }

        $offset = $offsetFromLine;
        for ($i=1; $i < $lineNumber; $i++) {
            $offset += (mb_strlen($lines[$i-1]) + mb_strlen($separator));
        }

        return $offset;
    }
}

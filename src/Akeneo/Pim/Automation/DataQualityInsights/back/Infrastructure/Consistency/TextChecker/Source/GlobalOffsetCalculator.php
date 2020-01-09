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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GlobalOffsetCalculator
{
    public function compute(string $source, int $lineNumber, int $offsetLine, string $separator = "\n"): int
    {
        if ($lineNumber < 0 || $offsetLine < 0) {
            return 0;
        }

        $lines = explode($separator, $source);
        if ($lineNumber > count($lines)) {
            return strlen($source);
        }

        $offset = $offsetLine;
        for ($i=1; $i < $lineNumber; $i++) {
            $offset += (strlen($lines[$i-1]) + strlen($separator));
        }

        return $offset;
    }
}

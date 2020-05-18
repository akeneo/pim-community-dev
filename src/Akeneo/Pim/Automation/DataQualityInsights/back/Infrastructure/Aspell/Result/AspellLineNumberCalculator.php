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
class AspellLineNumberCalculator
{
    public function compute(string $source, int $initialResultLineNumber, int $offsetFromLine, string $word, string $separator = "\n"): int
    {
        if ($initialResultLineNumber < 0) {
            return 0;
        }

        $lines = explode($separator, $source);
        $expectedLine = $initialResultLineNumber;

        foreach ($lines as $index => $line) {
            $currentLine = $index + 1;

            if (mb_strlen($line) === 0) {
                $expectedLine++;
            }

            if ($currentLine < $expectedLine) {
                continue;
            }

            $testedWord = mb_substr($line, $offsetFromLine, strlen($word));

            if ($testedWord === $word) {
                return $currentLine;
            }
        }

        return $initialResultLineNumber;
    }
}

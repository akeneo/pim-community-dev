<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

class ComputeLowerCaseWordsRate implements ComputeCaseWordsRate
{
    private const POINTS_TO_SUBTRACT_PER_ERROR = 24;

    public function __invoke(?string $productValue): ?Rate
    {
        if ($productValue === null) {
            return null;
        }

        $productValue = strip_tags($productValue);

        if (trim($productValue) === '') {
            return null;
        }

        $matches = [];
        preg_match_all('~(?:(?:^\s*)|(?:[\.|\?|\!\:]\s+))[a-z]~', $productValue, $matches);

        $nbErrors = empty($matches) ? 0 : count($matches[0]);
        $score = max(0, 100 - $nbErrors * self::POINTS_TO_SUBTRACT_PER_ERROR);

        return new Rate($score);
    }
}

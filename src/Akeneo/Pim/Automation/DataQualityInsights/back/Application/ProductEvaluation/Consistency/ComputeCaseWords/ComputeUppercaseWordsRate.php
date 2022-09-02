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

class ComputeUppercaseWordsRate implements ComputeCaseWordsRate
{
    public function __invoke(?string $productValue): ?Rate
    {
        if ($productValue === null) {
            return null;
        }

        $productValue = strip_tags($productValue);

        if (trim($productValue) === '') {
            return null;
        }

        $anyKindOfLetterFromAnyLanguageRegex = '~\p{L}+~u';
        if (preg_match($anyKindOfLetterFromAnyLanguageRegex, $productValue) === 0) {
            return new Rate(100);
        }

        return new Rate(mb_strtoupper($productValue) === $productValue ? 0 : 100);
    }
}

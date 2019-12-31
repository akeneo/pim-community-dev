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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleChecker;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SupportedPrefixLocaleChecker implements SupportedLocaleChecker
{
    const AVAILABLE_LANGUAGE_CODE = ['en', 'es', 'de', 'fr'];

    public function isSupported(string $locale): bool
    {
        return array_reduce(self::AVAILABLE_LANGUAGE_CODE, function ($previous, $localePrefix) use ($locale) {
            $pattern = sprintf(
                '~^%s_[A-Z]{2}$~',
                $localePrefix
            );

            return $previous || (preg_match($pattern, $locale) === 1);
        }, false);
    }
}

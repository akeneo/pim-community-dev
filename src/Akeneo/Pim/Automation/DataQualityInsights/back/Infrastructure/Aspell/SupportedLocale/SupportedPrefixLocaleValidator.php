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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\SupportedLocale;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter\LocaleCodeByLanguageCodeFilterIterator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SupportedPrefixLocaleValidator implements SupportedLocaleValidator
{
    const AVAILABLE_LANGUAGE_CODE = ['en', 'es', 'de', 'fr', 'it', 'sv', 'da', 'nl', 'nn', 'nb'];

    private $allActivatedLocalesQuery;

    public function __construct(GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery)
    {
        $this->allActivatedLocalesQuery = $allActivatedLocalesQuery;
    }

    public function isSupported(LocaleCode $localeCode): bool
    {
        return array_reduce(self::AVAILABLE_LANGUAGE_CODE, function ($previous, $localePrefix) use ($localeCode) {
            $pattern = sprintf(
                '~^%s_[A-Z]{2}$~',
                $localePrefix
            );

            return $previous || (preg_match($pattern, $localeCode->__toString()) === 1);
        }, false);
    }

    public function getSupportedLocaleCollection(): \Generator
    {
        $allActivatedLocales = $this->allActivatedLocalesQuery->execute();

        foreach (self::AVAILABLE_LANGUAGE_CODE as $supportedLanguageCode) {
            $filteredLocalesCode = iterator_to_array(
                new LocaleCodeByLanguageCodeFilterIterator(
                    $allActivatedLocales->getIterator(),
                    new LanguageCode($supportedLanguageCode)
                )
            );

            if (count($filteredLocalesCode) === 0) {
                continue;
            }

            yield $supportedLanguageCode => new LocaleCollection($filteredLocalesCode);
        }
    }

    public function extractLanguageCode(LocaleCode $localeCode): ?LanguageCode
    {
        if (!$this->isSupported($localeCode)) {
            return null;
        }

        preg_match('~^(?<language_code>[a-z]{2})_[A-Z]{2}$~', $localeCode->__toString(), $matches);

        return new LanguageCode($matches['language_code']);
    }
}

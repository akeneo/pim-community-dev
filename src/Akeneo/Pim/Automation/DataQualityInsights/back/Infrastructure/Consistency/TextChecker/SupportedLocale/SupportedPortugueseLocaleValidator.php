<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\SupportedLocale;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

class SupportedPortugueseLocaleValidator implements SupportedLocaleValidator
{
    const AVAILABLE_PORTUGUESE_LOCALE_CODE_LIST = ['pt_BR'];
    /**
     * @var GetAllActivatedLocalesQueryInterface
     */
    private $allActivatedLocalesQuery;

    public function __construct(GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery)
    {
        $this->allActivatedLocalesQuery = $allActivatedLocalesQuery;
    }

    public function isSupported(LocaleCode $localeCode): bool
    {
        return in_array(strval($localeCode), self::AVAILABLE_PORTUGUESE_LOCALE_CODE_LIST);
    }

    public function getSupportedLocaleCollection(): \Generator
    {
        $allActivatedLocales = $this->allActivatedLocalesQuery->execute();

        foreach (self::AVAILABLE_PORTUGUESE_LOCALE_CODE_LIST as $locale) {
            $localeCode = new LocaleCode($locale);

            if ($allActivatedLocales->has($localeCode)) {
                yield $locale => new LocaleCollection([$localeCode]);
            }
        }
    }

    public function extractLanguageCode(LocaleCode $localeCode): ?LanguageCode
    {
        if (!$this->isSupported($localeCode)) {
            return null;
        }

        return new LanguageCode(strval($localeCode));
    }
}

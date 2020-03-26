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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

class ChainedSupportedLocaleValidator implements SupportedLocaleValidator
{
    /**
     * @var  SupportedLocaleValidator[]
     */
    private $supportedLocaleValidatorList;

    public function __construct(iterable $supportedLocaleValidatorList)
    {
        $this->supportedLocaleValidatorList = [];

        foreach ($supportedLocaleValidatorList as $supportedLocaleValidator) {
            $this->supportedLocaleValidatorList[] = $supportedLocaleValidator;
        }
    }

    public function isSupported(LocaleCode $localeCode): bool
    {
        return array_reduce($this->supportedLocaleValidatorList, function (bool $previous, SupportedLocaleValidator $supportedLocaleValidator) use ($localeCode) {
            return $previous || $supportedLocaleValidator->isSupported($localeCode);
        }, false);
    }

    public function getSupportedLocaleCollection(): \Generator
    {
        foreach ($this->supportedLocaleValidatorList as $supportedLocaleValidator) {
            yield from $supportedLocaleValidator->getSupportedLocaleCollection();
        }
    }

    public function extractLanguageCode(LocaleCode $localeCode): ?LanguageCode
    {
        return array_reduce($this->supportedLocaleValidatorList, function (?LanguageCode $previous, SupportedLocaleValidator $supportedLocaleValidator) use ($localeCode) {
            return $supportedLocaleValidator->extractLanguageCode($localeCode) ?? $previous;
        }, null);
    }
}

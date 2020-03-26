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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\SupportedLocale;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

class SupportedPrefixLocaleValidatorSpec extends ObjectBehavior
{
    public function let(
        GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery
    ) {
        $this->beConstructedWith($allActivatedLocalesQuery);
    }

    public function it_supports_locale()
    {
        $this->isSupported(new LocaleCode('en_US'))->shouldBe(true);
        $this->isSupported(new LocaleCode('fr_FR'))->shouldBe(true);
        $this->isSupported(new LocaleCode('es_ES'))->shouldBe(true);
        $this->isSupported(new LocaleCode('de_DE'))->shouldBe(true);

        $this->isSupported(new LocaleCode('en_GB'))->shouldBe(true);
        $this->isSupported(new LocaleCode('fr_CA'))->shouldBe(true);
        $this->isSupported(new LocaleCode('es_AR'))->shouldBe(true);
        $this->isSupported(new LocaleCode('de_CH'))->shouldBe(true);
    }

    public function it_does_not_support_locale()
    {
        $this->isSupported(new LocaleCode('uz_UZ'))->shouldBe(false);

        $this->isSupported(new LocaleCode('fr'))->shouldBe(false);
        $this->isSupported(new LocaleCode('frFR'))->shouldBe(false);
        $this->isSupported(new LocaleCode('fr-FR'))->shouldBe(false);
    }

    public function it_returns_a_collection_of_valid_locales(
        $allActivatedLocalesQuery
    ) {
        $allActivatedLocales = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
            new LocaleCode('fr_FR'),
            new LocaleCode('pt_PT'),
        ]);

        $expectedSupportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_GB'),
                new LocaleCode('en_US'),
            ]),
            'fr' => new LocaleCollection([
                new LocaleCode('fr_FR')
            ]),
        ];

        $allActivatedLocalesQuery->execute()->willReturn($allActivatedLocales);

        $this->getSupportedLocaleCollection()->shouldYieldLike($expectedSupportedLocaleCollection);
    }

    public function it_returns_language_code()
    {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $this->extractLanguageCode($localeCode)->shouldBeLike($languageCode);

        $localeCode = new LocaleCode('fr_FR');
        $languageCode = new LanguageCode('fr');

        $this->extractLanguageCode($localeCode)->shouldBeLike($languageCode);
    }

    public function it_does_not_return_language_code()
    {
        $localeCode = new LocaleCode('pr_PT');

        $this->extractLanguageCode($localeCode)->shouldBe(null);
    }
}

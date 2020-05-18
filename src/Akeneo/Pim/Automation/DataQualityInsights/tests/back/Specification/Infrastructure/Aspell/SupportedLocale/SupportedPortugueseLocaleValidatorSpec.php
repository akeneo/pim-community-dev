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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\SupportedLocale;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

class SupportedPortugueseLocaleValidatorSpec extends ObjectBehavior
{
    public function let(
        GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery
    ) {
        $this->beConstructedWith($allActivatedLocalesQuery);
    }

    public function it_supports_locale()
    {
        $this->isSupported(new LocaleCode('pt_BR'))->shouldBe(true);
    }

    public function it_does_not_support_locale()
    {
        $this->isSupported(new LocaleCode('pt'))->shouldBe(false);
        $this->isSupported(new LocaleCode('pt_PT'))->shouldBe(false);

        $this->isSupported(new LocaleCode('en_US'))->shouldBe(false);
        $this->isSupported(new LocaleCode('fr_FR'))->shouldBe(false);
        $this->isSupported(new LocaleCode('es_ES'))->shouldBe(false);
        $this->isSupported(new LocaleCode('de_DE'))->shouldBe(false);
    }

    public function it_returns_a_collection_of_valid_locales(
        $allActivatedLocalesQuery
    ) {
        $allActivatedLocales = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
            new LocaleCode('fr_FR'),
            new LocaleCode('pt_PT'),
            new LocaleCode('pt_BR'),
        ]);

        $expectedSupportedLocaleCollection = [
            'pt_BR' => new LocaleCollection([
                new LocaleCode('pt_BR')
            ]),
        ];

        $allActivatedLocalesQuery->execute()->willReturn($allActivatedLocales);

        $this->getSupportedLocaleCollection()->shouldYieldLike($expectedSupportedLocaleCollection);
    }

    public function it_returns_language_code()
    {
        $localeCode = new LocaleCode('pt_BR');
        $languageCode = new LanguageCode('pt_BR');

        $this->extractLanguageCode($localeCode)->shouldBeLike($languageCode);
    }

    public function it_does_not_return_language_code()
    {
        $localeCode = new LocaleCode('en_US');
        $this->extractLanguageCode($localeCode)->shouldBe(null);

        $localeCode = new LocaleCode('pt_PT');
        $this->extractLanguageCode($localeCode)->shouldBeLike(null);
    }
}

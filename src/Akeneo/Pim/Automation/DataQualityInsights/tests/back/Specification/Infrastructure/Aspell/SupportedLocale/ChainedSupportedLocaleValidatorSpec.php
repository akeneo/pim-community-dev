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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class ChainedSupportedLocaleValidatorSpec extends ObjectBehavior
{
    public function let(
        SupportedLocaleValidator $supportedLocaleValidatorAlpha,
        SupportedLocaleValidator $supportedLocaleValidatorBeta
    ) {
        $this->beConstructedWith([$supportedLocaleValidatorAlpha, $supportedLocaleValidatorBeta]);
    }

    public function it_supports_locale(
        $supportedLocaleValidatorAlpha,
        $supportedLocaleValidatorBeta
    ) {
        $locales = [
            'en_US' => new LocaleCode('en_US'),
            'es_ES' => new LocaleCode('es_ES'),
            'fr_FR' => new LocaleCode('fr_FR'),
        ];

        $supportedLocaleValidatorAlpha->isSupported($locales['en_US'])->willReturn(true);
        $supportedLocaleValidatorBeta->isSupported($locales['en_US'])->willReturn(false);
        $this->isSupported($locales['en_US'])->shouldBe(true);

        $supportedLocaleValidatorAlpha->isSupported($locales['fr_FR'])->willReturn(true);
        $supportedLocaleValidatorBeta->isSupported($locales['fr_FR'])->willReturn(true);
        $this->isSupported($locales['fr_FR'])->shouldBe(true);

        $supportedLocaleValidatorAlpha->isSupported($locales['es_ES'])->willReturn(false);
        $supportedLocaleValidatorBeta->isSupported($locales['es_ES'])->willReturn(true);
        $this->isSupported($locales['es_ES'])->shouldBe(true);
    }

    public function it_does_not_support_locale(
        $supportedLocaleValidatorAlpha,
        $supportedLocaleValidatorBeta
    ) {

        $locales = [
            'uz_UZ' => new LocaleCode('uz_UZ'),
        ];

        $supportedLocaleValidatorAlpha->isSupported($locales['uz_UZ'])->willReturn(false);
        $supportedLocaleValidatorBeta->isSupported($locales['uz_UZ'])->willReturn(false);
        $this->isSupported($locales['uz_UZ'])->shouldBe(false);
    }

    public function it_returns_a_collection_of_valid_locales(
        $supportedLocaleValidatorAlpha,
        $supportedLocaleValidatorBeta
    ) {
        $supportedLocaleCollectionAlpha = [
            'en' => new LocaleCollection([
                new LocaleCode('en_GB'),
                new LocaleCode('en_US'),
            ]),
            'fr' => new LocaleCollection([
                new LocaleCode('fr_FR')
            ]),
        ];

        $supportedLocaleCollectionBeta = [
            'pt_BR' => new LocaleCollection([
                new LocaleCode('pt_BR'),
            ]),
        ];

        $expectedSupportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_GB'),
                new LocaleCode('en_US'),
            ]),
            'fr' => new LocaleCollection([
                new LocaleCode('fr_FR')
            ]),
            'pt_BR' => new LocaleCollection([
                new LocaleCode('pt_BR'),
            ]),
        ];

        $supportedLocaleValidatorAlpha->getSupportedLocaleCollection()->willYield($supportedLocaleCollectionAlpha);
        $supportedLocaleValidatorBeta->getSupportedLocaleCollection()->willYield($supportedLocaleCollectionBeta);

        $this->getSupportedLocaleCollection()->shouldYieldLike($expectedSupportedLocaleCollection);
    }

    public function it_overrides_collection_of_valid_locales_when_validators_return_duplicated_keys(
        $supportedLocaleValidatorAlpha,
        $supportedLocaleValidatorBeta
    ) {
        $supportedLocaleCollectionAlpha = [
            'en' => new LocaleCollection([
                new LocaleCode('en_GB'),
                new LocaleCode('en_US'),
            ]),
        ];

        $supportedLocaleCollectionBeta = [
            'en' => new LocaleCollection([
                new LocaleCode('en_CA'),
            ]),
        ];

        $expectedArraySupportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_CA'),
            ]),
        ];

        $supportedLocaleValidatorAlpha->getSupportedLocaleCollection()->willYield($supportedLocaleCollectionAlpha);
        $supportedLocaleValidatorBeta->getSupportedLocaleCollection()->willYield($supportedLocaleCollectionBeta);

        $result = iterator_to_array($this->getSupportedLocaleCollection()->getWrappedObject());

        Assert::eq($result, $expectedArraySupportedLocaleCollection);
    }

    public function it_returns_language_code_when_one_of_the_validators_can_handle_it(
        SupportedLocaleValidator $supportedLocaleValidatorAlpha,
        SupportedLocaleValidator $supportedLocaleValidatorBeta
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidatorAlpha->extractLanguageCode($localeCode)->willReturn($languageCode);
        $supportedLocaleValidatorBeta->extractLanguageCode($localeCode)->willReturn(null);

        $this->extractLanguageCode($localeCode)->shouldBe($languageCode);

        $supportedLocaleValidatorAlpha->extractLanguageCode($localeCode)->willReturn(null);
        $supportedLocaleValidatorBeta->extractLanguageCode($localeCode)->willReturn($languageCode);

        $this->extractLanguageCode($localeCode)->shouldBe($languageCode);
    }

    public function it_does_not_return_language_code_when_any_validators_can_handle_it(
        SupportedLocaleValidator $supportedLocaleValidatorAlpha,
        SupportedLocaleValidator $supportedLocaleValidatorBeta
    ) {
        $localeCode = new LocaleCode('en_US');

        $supportedLocaleValidatorAlpha->extractLanguageCode($localeCode)->willReturn(null);
        $supportedLocaleValidatorBeta->extractLanguageCode($localeCode)->willReturn(null);

        $this->extractLanguageCode($localeCode)->shouldBe(null);
    }
}

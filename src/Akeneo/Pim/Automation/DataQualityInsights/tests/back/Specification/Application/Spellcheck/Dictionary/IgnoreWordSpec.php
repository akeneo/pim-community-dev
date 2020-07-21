<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IgnoreWordSpec extends ObjectBehavior
{
    public function let(
        SupportedLocaleValidator $supportedLocaleValidator,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository
    ) {
        $this->beConstructedWith($supportedLocaleValidator, $textCheckerDictionaryRepository);
    }

    public function it_ignores_word(
        $supportedLocaleValidator,
        $textCheckerDictionaryRepository
    ) {
        $word = new DictionaryWord('anyword');
        $locale = new LocaleCode('en_US');

        $supportedLocaleValidator->isSupported($locale)->willReturn(true);
        $textCheckerDictionaryRepository->save(Argument::any())->shouldBeCalled();

        $this->execute($word, $locale);
    }

    public function it_does_not_ignore_word_when_locale_is_not_supported(
        $supportedLocaleValidator,
        $textCheckerDictionaryRepository
    ) {
        $word = new DictionaryWord('anyword');
        $locale = new LocaleCode('zz_ZZ');

        $supportedLocaleValidator->isSupported($locale)->willReturn(false);
        $textCheckerDictionaryRepository->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('execute',  [$word, $locale]);
    }
}

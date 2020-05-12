<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionaryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class AspellDictionaryGeneratorSpec extends ObjectBehavior
{
    public function let(
        AspellDictionaryInterface $aspellDictionary,
        SupportedLocaleValidator $supportedLocaleValidator,
        Clock $clock
    ) {
        $this->beConstructedWith($aspellDictionary, $supportedLocaleValidator, $clock);
    }

    public function it_generate_a_dictionary_by_language_code(
        $aspellDictionary,
        $supportedLocaleValidator,
        DictionarySource $dictionarySource
    ) {
        $supportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_US'),
                new LocaleCode('en_GB'),
            ]),
        ];

        $supportedLocaleValidator->getSupportedLocaleCollection()->willYield($supportedLocaleCollection);

        $enDictionary = new Dictionary(['silence', 'is', 'gold']);
        $dictionarySource->getDictionary($supportedLocaleCollection['en'])->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('en'))->shouldBeCalled();

        $this->ignoreCheckTimestamp()->generate($dictionarySource);
    }

    public function it_generate_severals_dictionaries_by_language_code(
        $aspellDictionary,
        $supportedLocaleValidator,
        DictionarySource $dictionarySource
    ) {
        $supportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_US'),
                new LocaleCode('en_GB'),
            ]),
            'fr' => new LocaleCollection([
                new LocaleCode('fr_FR'),
            ]),
        ];

        $supportedLocaleValidator->getSupportedLocaleCollection()->willYield($supportedLocaleCollection);

        $enDictionary = new Dictionary(['silence', 'is', 'gold']);
        $dictionarySource->getDictionary($supportedLocaleCollection['en'])->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('en'))->shouldBeCalled();

        $enDictionary = new Dictionary(['silence', 'est', 'or']);
        $dictionarySource->getDictionary($supportedLocaleCollection['fr'])->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('fr'))->shouldBeCalled();

        $this->ignoreCheckTimestamp()->generate($dictionarySource);
    }

    public function it_skips_the_generation_if_dictionary_not_enough_old(
        $aspellDictionary,
        $supportedLocaleValidator,
        $clock,
        DictionarySource $dictionarySource
    ) {
        $supportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_US'),
                new LocaleCode('en_GB'),
            ]),
        ];

        $supportedLocaleValidator->getSupportedLocaleCollection()->willYield($supportedLocaleCollection);

        $aspellDictionary->getSharedDictionaryTimestamp(new LanguageCode('en'))->willReturn(1234);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1234);
        $now =(new \DateTimeImmutable())->setTimestamp(1235);

        $clock->fromTimestamp(1234)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $dictionarySource->getDictionary($supportedLocaleCollection['en'])->shouldNotBeCalled();
        $aspellDictionary->persistDictionaryToSharedFilesystem(Argument::any())->shouldNotBeCalled();

        $this->generate($dictionarySource);
    }

    public function it_generate_a_dictionary_by_language_code_by_checking_the_timestamp(
        $aspellDictionary,
        $supportedLocaleValidator,
        $clock,
        DictionarySource $dictionarySource
    ) {
        $supportedLocaleCollection = [
            'en' => new LocaleCollection([
                new LocaleCode('en_US'),
                new LocaleCode('en_GB'),
            ]),
        ];

        $supportedLocaleValidator->getSupportedLocaleCollection()->willYield($supportedLocaleCollection);

        $aspellDictionary->getSharedDictionaryTimestamp(new LanguageCode('en'))->willReturn(1234);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1234);
        $now =(new \DateTimeImmutable())->setTimestamp(123456789);

        $clock->fromTimestamp(1234)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $enDictionary = new Dictionary(['silence', 'is', 'gold']);
        $dictionarySource->getDictionary($supportedLocaleCollection['en'])->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('en'))->shouldBeCalled();

        $this->generate($dictionarySource);
    }
}

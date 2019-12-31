<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionary;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class AspellDictionaryGeneratorSpec extends ObjectBehavior
{
    public function let(
        AspellDictionary $aspellDictionary,
        GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery,
        Clock $clock
    ) {
        $this->beConstructedWith($aspellDictionary, $allActivatedLocalesQuery, $clock);
    }

    public function it_generate_a_dictionary_by_language_code(
        $aspellDictionary,
        $allActivatedLocalesQuery,
        DictionarySource $dictionarySource
    ) {
        $localeCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $allActivatedLocalesQuery->execute()->willReturn($localeCollection);

        $filteredEnLocaleCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $enDictionary = new Dictionary(['silence', 'is', 'gold']);
        $dictionarySource->getDictionary($filteredEnLocaleCollection)->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('en'))->shouldBeCalled();

        $this->ignoreCheckTimestamp()->generate($dictionarySource);
    }

    public function it_generate_severals_dictionaries_by_language_code(
        $aspellDictionary,
        $allActivatedLocalesQuery,
        DictionarySource $dictionarySource
    ) {
        $localeCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
            new LocaleCode('fr_FR'),
        ]);

        $allActivatedLocalesQuery->execute()->willReturn($localeCollection);

        $filteredEnLocaleCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $enDictionary = new Dictionary(['silence', 'is', 'gold']);
        $dictionarySource->getDictionary($filteredEnLocaleCollection)->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('en'))->shouldBeCalled();

        $filteredEnLocaleCollection = new LocaleCollection([
            new LocaleCode('fr_FR'),
        ]);

        $enDictionary = new Dictionary(['silence', 'est', 'or']);
        $dictionarySource->getDictionary($filteredEnLocaleCollection)->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('fr'))->shouldBeCalled();

        $this->ignoreCheckTimestamp()->generate($dictionarySource);
    }

    public function it_skips_the_generation_if_dictionary_not_enough_old(
        $aspellDictionary,
        $allActivatedLocalesQuery,
        $clock,
        DictionarySource $dictionarySource
    ) {
        $localeCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $allActivatedLocalesQuery->execute()->willReturn($localeCollection);

        $filteredEnLocaleCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $aspellDictionary->getSharedDictionaryTimestamp(new LanguageCode('en'))->willReturn(1234);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1234);
        $now =(new \DateTimeImmutable())->setTimestamp(1235);

        $clock->fromTimestamp(1234)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $dictionarySource->getDictionary($filteredEnLocaleCollection)->shouldNotBeCalled();
        $aspellDictionary->persistDictionaryToSharedFilesystem(Argument::any())->shouldNotBeCalled();

        $this->generate($dictionarySource);
    }

    public function it_generate_a_dictionary_by_language_code_by_checking_the_timestamp(
        $aspellDictionary,
        $allActivatedLocalesQuery,
        $clock,
        DictionarySource $dictionarySource
    ) {
        $localeCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $allActivatedLocalesQuery->execute()->willReturn($localeCollection);

        $filteredEnLocaleCollection = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $aspellDictionary->getSharedDictionaryTimestamp(new LanguageCode('en'))->willReturn(1234);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1234);
        $now =(new \DateTimeImmutable())->setTimestamp(123456789);

        $clock->fromTimestamp(1234)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $enDictionary = new Dictionary(['silence', 'is', 'gold']);
        $dictionarySource->getDictionary($filteredEnLocaleCollection)->willReturn($enDictionary);

        $aspellDictionary->persistDictionaryToSharedFilesystem($enDictionary, new LanguageCode('en'))->shouldBeCalled();

        $this->generate($dictionarySource);
    }
}

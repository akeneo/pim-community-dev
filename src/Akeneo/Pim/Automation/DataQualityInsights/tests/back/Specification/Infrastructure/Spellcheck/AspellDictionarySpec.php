<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\AspellDictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\AspellDictionaryLocalFilesystemInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Dictionary\SpellerDictionary;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class AspellDictionarySpec extends ObjectBehavior
{
    public function let(
        FilesystemProvider $filesystemProvider,
        Clock $clock,
        AspellDictionaryLocalFilesystemInterface $localFilesystemProvider,
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->beConstructedWith($filesystemProvider, $clock, $localFilesystemProvider, $supportedLocaleValidator);

        $localFilesystemProvider->getFilesystem()->willReturn($localFilesystem);
        $localFilesystemProvider->getAbsoluteRootPath()->willReturn('/tmp');
        $filesystemProvider->getFilesystem('dataQualityInsightsSharedAdapter')->willReturn($sharedFilesystem);
    }

    public function it_is_initializable(
        $localFilesystemProvider,
        FilesystemOperator $localFilesystem
    ) {
        $localFilesystemProvider->getFilesystem()->willReturn($localFilesystem);
        $this->shouldBeAnInstanceOf(AspellDictionary::class);
    }

    public function it_persist_dictionary_to_shared_filesystem(
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem
    ) {
        $dictionary = new Dictionary(['word']);
        $languageCode = new LanguageCode('en');

        $sharedFilesystem->writeStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            Argument::type('resource')
        )->shouldBeCalled();

        $this->persistDictionaryToSharedFilesystem($dictionary, $languageCode);
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path(
        Clock $clock,
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $localFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $localFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1577462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new SpellerDictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_gets_up_to_date_speller_dictionary_by_downloading_it_if_does_not_exists_locally(
        Clock $clock,
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false, false, true);
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $resource = fopen(__FILE__, 'r');
        $sharedFilesystem->readStream('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn($resource);
        $localFilesystem->writeStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            $resource
        )->shouldBeCalled();

        $localFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1577462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new SpellerDictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_return_null_if_there_is_no_dictionary_for_the_given_locale(
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);
        $localFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);

        $this->getUpToDateSpellerDictionary(new LocaleCode('en_US'))->shouldReturn(null);
    }

    public function it_return_null_if_there_is_no_supported_language_for_the_given_locale(
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn(null);

        $this->getUpToDateSpellerDictionary($localeCode)->shouldReturn(null);
    }

    public function it_throws_exception_if_unable_to_download_dictionary(
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $sharedFilesystem->readStream('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn('crapy_thing');
        $localFilesystem->writeStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            Argument::any()
        )->shouldNotBeCalled();

        $this->shouldThrow(UnableToRetrieveDictionaryException::class)->during('getUpToDateSpellerDictionary', [$localeCode]);
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_it_if_is_older_than_a_day(
        Clock $clock,
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $localFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);
        $sharedFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1677462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $sharedFilesystem->readStream(Argument::any())->shouldNotBeCalled();
        $localFilesystem->writeStream(Argument::cetera())->shouldNotBeCalled();

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new SpellerDictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_if_it_is_older_than_a_day_and_if_shared_one_is_newer(
        Clock $clock,
        FilesystemOperator $localFilesystem,
        FilesystemOperator $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $localFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);
        $sharedFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1777462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1677462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $resource = fopen(__FILE__, 'r');
        $sharedFilesystem->readStream('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn($resource);
        $localFilesystem->writeStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            $resource
        )->shouldBeCalled();

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new SpellerDictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_gets_shared_dictionary_timestamp_if_exists(FilesystemOperator $sharedFilesystem)
    {
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $sharedFilesystem->lastModified('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1234);

        $this->getSharedDictionaryTimestamp(new LanguageCode('en'))->shouldReturn(1234);
    }

    public function it_returns_null_if_shared_dictionary_timestamp_do_no_exists(FilesystemOperator $sharedFilesystem)
    {
        $sharedFilesystem->fileExists('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);

        $this->getSharedDictionaryTimestamp(new LanguageCode('en'))->shouldReturn(null);
    }
}

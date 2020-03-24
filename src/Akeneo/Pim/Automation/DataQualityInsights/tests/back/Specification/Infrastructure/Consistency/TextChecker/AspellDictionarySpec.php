<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionaryLocalFilesystemInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Mekras\Speller;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class AspellDictionarySpec extends ObjectBehavior
{
    public function let(
        MountManager $mountManager,
        Clock $clock,
        AspellDictionaryLocalFilesystemInterface $localFilesystemProvider,
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->beConstructedWith($mountManager, $clock, $localFilesystemProvider, $supportedLocaleValidator);

        $localFilesystemProvider->getFilesystem()->willReturn($localFilesystem);
        $localFilesystemProvider->getAbsoluteRootPath()->willReturn('/tmp');
        $mountManager->getFilesystem('dataQualityInsightsSharedAdapter')->willReturn($sharedFilesystem);
    }

    public function it_is_initializable(
        $localFilesystemProvider,
        FilesystemInterface $localFilesystem
    ) {
        $localFilesystemProvider->getFilesystem()->willReturn($localFilesystem);
        $this->shouldBeAnInstanceOf(AspellDictionary::class);
    }

    public function it_persist_dictionary_to_shared_filesystem(
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem
    ) {
        $dictionary = new Dictionary(['word']);
        $languageCode = new LanguageCode('en');

        $sharedFilesystem->putStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            Argument::type('resource')
        )->shouldBeCalled();

        $this->persistDictionaryToSharedFilesystem($dictionary, $languageCode);
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path(
        Clock $clock,
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $localFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $localFilesystem->getTimestamp('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1577462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new Speller\Dictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_gets_up_to_date_speller_dictionary_by_downloading_it_if_does_not_exists_locally(
        Clock $clock,
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false, false, true);
        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $resource = fopen(__FILE__, 'r');
        $sharedFilesystem->readStream('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn($resource);
        $localFilesystem->putStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            $resource
        )->shouldBeCalled();

        $localFilesystem->getTimestamp('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1577462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new Speller\Dictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_return_null_if_there_is_no_dictionary_for_the_given_locale(
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);
        $localFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);
        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);

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
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);
        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $sharedFilesystem->readStream('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn('crapy_thing');
        $localFilesystem->putStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            Argument::any()
        )->shouldNotBeCalled();

        $this->shouldThrow(UnableToRetrieveDictionaryException::class)->during('getUpToDateSpellerDictionary', [$localeCode]);
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_it_if_is_older_than_a_day(
        Clock $clock,
        FilesystemInterface $localFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $localFilesystem->getTimestamp('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1677462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $localFilesystem->putStream(Argument::any())->shouldNotBeCalled();

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new Speller\Dictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_if_it_is_older_than_a_day_and_if_shared_one_is_newer(
        Clock $clock,
        FilesystemInterface $localFilesystem,
        FilesystemInterface $sharedFilesystem,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $localeCode = new LocaleCode('en_US');
        $languageCode = new LanguageCode('en');

        $supportedLocaleValidator->extractLanguageCode($localeCode)->willReturn($languageCode);

        $localFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $localFilesystem->getTimestamp('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);
        $sharedFilesystem->getTimestamp('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1777462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1677462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $resource = fopen(__FILE__, 'r');
        $sharedFilesystem->readStream('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn($resource);
        $localFilesystem->putStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            $resource
        )->shouldBeCalled();

        $this->getUpToDateSpellerDictionary($localeCode)
            ->shouldBeLike(new Speller\Dictionary('/tmp/consistency/text_checker/aspell/custom-dictionary-en.pws'));
    }

    public function it_gets_shared_dictionary_timestamp_if_exists(FilesystemInterface $sharedFilesystem)
    {
        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $sharedFilesystem->getTimestamp('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1234);

        $this->getSharedDictionaryTimestamp(new LanguageCode('en'))->shouldReturn(1234);
    }

    public function it_returns_null_if_shared_dictionary_timestamp_do_no_exists(FilesystemInterface $sharedFilesystem)
    {
        $sharedFilesystem->has('consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);

        $this->getSharedDictionaryTimestamp(new LanguageCode('en'))->shouldReturn(null);
    }
}

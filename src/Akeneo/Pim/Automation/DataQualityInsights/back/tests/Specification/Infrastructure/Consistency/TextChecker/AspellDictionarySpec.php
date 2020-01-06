<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\DictionaryNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionary;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class AspellDictionarySpec extends ObjectBehavior
{
    public function let(MountManager $mountManager, Clock $clock)
    {
        $this->beConstructedWith($mountManager, $clock);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(AspellDictionary::class);
    }

    public function it_persist_dictionary_to_shared_filesystem(
        $mountManager,
        FilesystemInterface $sharedFilesystem
    ) {
        $dictionary = new Dictionary(['word']);
        $languageCode = new LanguageCode('en');

        $mountManager->getFilesystem('dataQualityInsightsSharedAdapter')->willReturn($sharedFilesystem);
        $sharedFilesystem->putStream(
            'consistency/text_checker/aspell/custom-dictionary-en.pws',
            Argument::type('resource')
        )->shouldBeCalled();

        $this->persistDictionaryToSharedFilesystem($dictionary, $languageCode);
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path(
        $mountManager,
        $clock
    ) {
        $mountManager->has('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $mountManager->getTimestamp('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1577462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $this->getUpToDateLocalDictionaryRelativeFilePath(new LocaleCode('en_US'))->shouldReturn('consistency/text_checker/aspell/custom-dictionary-en.pws');
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_it_if_does_not_exists(
        $mountManager,
        $clock
    ) {
        $mountManager->has('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false, true);
        $mountManager->has('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $mountManager->readStream('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn('a_resource');
        $mountManager->putStream(
            'dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws',
            'a_resource'
        )->shouldBeCalled();

        $mountManager->getTimestamp('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1577462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $this->getUpToDateLocalDictionaryRelativeFilePath(new LocaleCode('en_US'))->shouldReturn('consistency/text_checker/aspell/custom-dictionary-en.pws');
    }

    public function it_throws_exception_if_unable_to_download_dictionary(
        $mountManager
    ) {
        $mountManager->has('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false, false);
        $mountManager->has('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $mountManager->readStream('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn('a_resource');
        $mountManager->putStream(
            'dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws',
            'a_resource'
        )->shouldBeCalled();

        $this->shouldThrow(DictionaryNotFoundException::class)->during('getUpToDateLocalDictionaryRelativeFilePath', [new LocaleCode('en_US')]);
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_it_if_is_older_than_a_day(
        $mountManager,
        $clock
    ) {
        $mountManager->has('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $mountManager->getTimestamp('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);
        $mountManager->getTimestamp('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1677462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $mountManager->putStream(Argument::any())->shouldNotBeCalled();

        $this->getUpToDateLocalDictionaryRelativeFilePath(new LocaleCode('en_US'))->shouldReturn('consistency/text_checker/aspell/custom-dictionary-en.pws');
    }

    public function it_gets_up_to_date_local_dictionary_relative_file_path_by_downloading_if_it_is_older_than_a_day_and_if_shared_one_is_newer(
        $mountManager,
        $clock
    ) {
        $mountManager->has('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true, true);
        $mountManager->has('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);

        $mountManager->getTimestamp('dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1577462283);
        $mountManager->getTimestamp('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1777462283);

        $fileDate = (new \DateTimeImmutable())->setTimestamp(1577462283);
        $now = (new \DateTimeImmutable())->setTimestamp(1677462283);

        $clock->fromTimestamp(1577462283)->willReturn($fileDate);
        $clock->getCurrentTime()->willReturn($now);

        $mountManager->readStream('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn('a_resource');
        $mountManager->putStream(
            'dataQualityInsightsLocalAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws',
            'a_resource'
        )->shouldBeCalled();

        $this->getUpToDateLocalDictionaryRelativeFilePath(new LocaleCode('en_US'))->shouldReturn('consistency/text_checker/aspell/custom-dictionary-en.pws');
    }

    public function it_gets_shared_dictionary_timestamp_if_exists($mountManager)
    {
        $mountManager->has('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(true);
        $mountManager->getTimestamp('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(1234);

        $this->getSharedDictionaryTimestamp(new LanguageCode('en'))->shouldReturn(1234);
    }

    public function it_returns_null_if_shared_dictionary_timestamp_do_no_exists($mountManager)
    {
        $mountManager->has('dataQualityInsightsSharedAdapter://consistency/text_checker/aspell/custom-dictionary-en.pws')->willReturn(false);

        $this->getSharedDictionaryTimestamp(new LanguageCode('en'))->shouldReturn(null);
    }
}

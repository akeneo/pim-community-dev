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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionaryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\TextSource;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Exception\RuntimeException;
use Mekras\Speller\Issue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellCheckerSpec extends ObjectBehavior
{
    public function let(
        Aspell $speller, AspellDictionaryInterface $aspellDictionary, AspellGlobalOffsetCalculator $globalOffsetCalculator,
        AspellLineNumberCalculator $lineNumberCalculator,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository
    ) {
        $this->beConstructedWith($speller, $aspellDictionary, $globalOffsetCalculator, $lineNumberCalculator, $textCheckerDictionaryRepository);
    }

    public function it_checks_test(
        $speller,
        $aspellDictionary,
        $textCheckerDictionaryRepository,
        $globalOffsetCalculator,
        $lineNumberCalculator
    ) {
        $text = 'Typos hapen.';
        $source = new TextSource($text);
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pws');

        $issue = new Issue('hapen', 'ANY_ASPELL_RETURN_CODE');
        $issue->offset = 0;
        $issue->line = 1;
        $aspellCheckResult = [$issue];

        $speller->checkText($source, ['en_US'])->willReturn($aspellCheckResult);

        $textCheckerDictionaryRepository->findByLocaleCode($localeCode)->willReturn([]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(1);
    }

    public function it_checks_test_without_issue(
        $speller,
        $aspellDictionary,
        $textCheckerDictionaryRepository,
        $globalOffsetCalculator,
        $lineNumberCalculator
    ) {
        $text = 'Typos happen.';
        $source = new TextSource($text);
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pw');

        $aspellCheckResult = [
        ];

        $speller->checkText($source, ['en_US'])->willReturn($aspellCheckResult);

        $textCheckerDictionaryRepository->findByLocaleCode($localeCode)->willReturn([]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }

    public function it_checks_test_without_issue_with_user_generated_dictionary(
        $speller,
        $aspellDictionary,
        $textCheckerDictionaryRepository,
        $globalOffsetCalculator,
        $lineNumberCalculator
    ) {
        $text = 'A text with Dior brand name not known by regular dictionary';
        $source = new TextSource($text);
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pw');


        $issue = new Issue('Dior', 'ANY_ASPELL_RETURN_CODE');
        $issue->offset = 0;
        $issue->line = 1;
        $aspellCheckResult = [$issue];

        $speller->checkText($source, ['en_US'])->willReturn($aspellCheckResult);

        $textCheckerDictionaryRepository->findByLocaleCode($localeCode)->willReturn([
            new TextCheckerDictionaryWord(
                new LocaleCode('en_US'),
                new DictionaryWord('Dior')
            )
        ]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }

    public function it_throws_an_exception_if_an_error_occurs_during_text_checking(
        $speller,
        $aspellDictionary
    ) {
        $text = 'Typos hapen.';
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pws');

        $speller->checkText(Argument::cetera())->willThrow(new RuntimeException());

        $this->shouldThrow(TextCheckFailedException::class)->during('check', [$text, $localeCode]);
    }
}

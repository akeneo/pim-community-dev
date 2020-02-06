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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionaryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\GlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Issue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellCheckerSpec extends ObjectBehavior
{
    public function let(AspellDictionaryInterface $aspellDictionary, GlobalOffsetCalculator $globalOffsetCalculator, TextCheckerDictionaryRepository $textCheckerDictionaryRepository)
    {
        $this->beConstructedWith('aspell', $aspellDictionary, $globalOffsetCalculator, $textCheckerDictionaryRepository);
    }

    public function it_checks_test(
        Aspell $speller,
        $aspellDictionary,
        $textCheckerDictionaryRepository,
        $globalOffsetCalculator
    ) {
        $text = 'Typos hapen.';
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pws');

        $aspellCheckResult = [
            new Issue('hapen', 'ANY_ASPELL_RETURN_CODE'),
        ];

        $speller->checkText($text, [$localeCode->__toString()])->willReturn($aspellCheckResult);

        $textCheckerDictionaryRepository->findByLocaleCode($localeCode)->willReturn([]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(1);
    }

    public function it_checks_test_without_issue(
        Aspell $speller,
        $aspellDictionary,
        $textCheckerDictionaryRepository,
        $globalOffsetCalculator
    ) {
        $text = 'Typos happen.';
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pw');

        $aspellCheckResult = [
        ];

        $speller->checkText($text, [$localeCode->__toString()])->willReturn($aspellCheckResult);

        $textCheckerDictionaryRepository->findByLocaleCode($localeCode)->willReturn([]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }

    public function it_checks_test_without_issue_with_user_generated_dictionary(
        Aspell $speller,
        $aspellDictionary,
        $textCheckerDictionaryRepository,
        $globalOffsetCalculator
    ) {
        $text = 'A text with Dior brand name not known by regular dictionary';
        $localeCode = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode)->willReturn('/an/absolute/filepath-en.pw');

        $aspellCheckResult = [
            new Issue('Dior', 'ANY_ASPELL_RETURN_CODE'),
        ];

        $speller->checkText($text, [$localeCode->__toString()])->willReturn($aspellCheckResult);

        $textCheckerDictionaryRepository->findByLocaleCode($localeCode)->willReturn([
            new TextCheckerDictionaryWord(
                new LocaleCode('en_US'),
                new DictionaryWord('Dior')
            )
        ]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }
}

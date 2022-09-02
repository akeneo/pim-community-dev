<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Source\TextSource;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use PhpSpellcheck\Misspelling;
use PhpSpellcheck\Spellchecker\SpellcheckerInterface;
use Prophecy\Argument;

class TigitzSpellerSpec extends ObjectBehavior
{
    public function let(
        SpellcheckerInterface $spellchecker,
        AspellGlobalOffsetCalculator    $globalOffsetCalculator,
        AspellLineNumberCalculator      $lineNumberCalculator,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository,
    )
    {
        $this->beConstructedWith(
            $spellchecker,
            $globalOffsetCalculator,
            $lineNumberCalculator,
            $textCheckerDictionaryRepository,
        );
    }

    public function it_checks_test(
        $spellchecker,
        $globalOffsetCalculator,
        $lineNumberCalculator,
        $textCheckerDictionaryRepository,
    ): void
    {
        $source = new TextSource('Typos hapen.');
        $issues = [new Misspelling('hapen', 6, 1, ['happen'])];
        $spellchecker->check(Argument::cetera())->willReturn($issues);

        $expectedResult = new TextCheckResultCollection();
        $expectedResult->add(
            new TextCheckResult(
                $issues[0]->getWord(),
                TextCheckResult::SPELLING_ISSUE_TYPE,
                6,
                6,
                1,
                $issues[0]->getSuggestions()
            )
        );
        $lineNumberCalculator->compute(Argument::cetera())->willReturn(1);
        $globalOffsetCalculator->compute(Argument::cetera())->willReturn(6);

        $localeCode = new LocaleCode('en_US');
        $textCheckerDictionaryRepository->filterExistingWords(
            $localeCode,
            [new DictionaryWord('hapen')]
        )->willReturn([]);


        $this->check($source, $localeCode)->shouldHaveExpectedResult();
    }

    public function it_throws_an_exception_if_an_error_occurs_during_text_checking(
        $spellchecker,
        $globalOffsetCalculator,
        $lineNumberCalculator,
        $textCheckerDictionaryRepository,
    ): void
    {
        $text = 'Typos hapen.';
        $source = new TextSource($text);
        $spellchecker
            ->check(Argument::cetera())
            ->willThrow(new \Exception());

        $localeCode = new LocaleCode('en_US');
        $textCheckerDictionaryRepository->filterExistingWords(
            $localeCode,
            [new DictionaryWord('hapen')]
        )->willReturn([]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(TextCheckFailedException::class)
            ->during('check', [$source, $localeCode]);
    }

    public function it_checks_test_with_issue_on_an_ignored_word(
        $spellchecker,
        $globalOffsetCalculator,
        $lineNumberCalculator,
        $textCheckerDictionaryRepository,
    )
    {
        $text = 'A text with Dior brand name not known by regular dictionary';
        $source = new TextSource($text);
        $localeCode = new LocaleCode('en_US');

        $issue = new Misspelling('Dior', 0, 1, []);
        $aspellCheckResult = [$issue];

        $spellchecker->check(Argument::cetera())->willReturn($aspellCheckResult);

        $dior = new DictionaryWord('Dior');
        $lowercaseDior = new DictionaryWord('dior');
        $textCheckerDictionaryRepository
            ->filterExistingWords($localeCode, [$dior])
            ->willReturn([$lowercaseDior]);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($source, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }

    public function getMatchers(): array
    {
        return [
            'haveExpectedResult' => function (TextCheckResultCollection $subject) {
                $subjectResult = $subject->normalize()[0];
                if ((!count($subjectResult['suggestions'])) > 0) {
                    throw new FailureException('No suggestion found for ' . $subjectResult['text']);
                }

                if (($subjectResult['offset']) !== 6 && ($subjectResult['globalOffset']) !== 6) {
                    throw new FailureException('Invalid offset position');
                }

                if (($subjectResult['line']) !== 1 && ($subjectResult['globalOffset']) !== 1) {
                    throw new FailureException('Invalid line position');
                }

                return true;
            }
        ];
    }
}

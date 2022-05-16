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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\SpellerInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use PhpSpec\ObjectBehavior;
use PhpSpellcheck\Misspelling;
use Prophecy\Argument;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class CheckerSpec extends ObjectBehavior
{
    public function let(
        SpellerInterface                $spellerProvider,
        AspellGlobalOffsetCalculator    $globalOffsetCalculator,
        AspellLineNumberCalculator      $lineNumberCalculator,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository
    )
    {
        $this->beConstructedWith(
            $spellerProvider,
            $globalOffsetCalculator,
            $lineNumberCalculator,
            $textCheckerDictionaryRepository
        );
    }

    public function it_checks_test(
        $spellerProvider,
        $globalOffsetCalculator,
        $lineNumberCalculator
    )
    {
        $text = 'Typos hapen.';
        $localeCode = new LocaleCode('en_US');

        $issue = new Misspelling('hapen', 0, 1, ['happen']);
        $expectedResult = new TextCheckResultCollection();
        $expectedResult->add(
            new TextCheckResult(
                $issue->getWord(),
                TextCheckResult::SPELLING_ISSUE_TYPE,
                6,
                $issue->getOffset(),
                $issue->getLineNumber(),
                $issue->getSuggestions()
            )
        );
        $spellerProvider->check(Argument::cetera())->willReturn($expectedResult);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($text, $localeCode);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
    }

    public function it_checks_test_throwing_exception(
        $spellerProvider,
        $globalOffsetCalculator,
        $lineNumberCalculator
    )
    {
        $spellerProvider->check(Argument::cetera())->willThrow(new TextCheckFailedException());

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();
        $lineNumberCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $text = 'A text with Dior brand name not known by regular dictionary';
        $localeCode = new LocaleCode('en_US');
        $this->shouldThrow(TextCheckFailedException::class)->during('check', [$text, $localeCode]);
    }
}

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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\GlobalOffsetCalculator;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Dictionary;
use Mekras\Speller\Issue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellCheckerSpec extends ObjectBehavior
{
    public function let(AspellDictionary $aspellDictionary, GlobalOffsetCalculator $globalOffsetCalculator)
    {
        $this->beConstructedWith('aspell', $aspellDictionary, '/an/absolute/path', $globalOffsetCalculator);
    }

    public function it_checks_test(
        Aspell $speller,
        $aspellDictionary,
        GlobalOffsetCalculator $globalOffsetCalculator
    ) {
        $text = 'Typos hapen.';
        $locale = 'en_US';

        $aspellDictionary->getUpToDateLocalDictionaryRelativeFilePath(new LocaleCode($locale))->willReturn('a/relative/filepath.pws');

        $aspellCheckResult = [
            new Issue('hapen', 'ANY_ASPELL_RETURN_CODE'),
        ];

        $speller->checkText($text, [$locale])->willReturn($aspellCheckResult);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldBeCalled();

        $result = $this->check($text, new LocaleCode($locale));

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(1);
    }

    public function it_checks_test_without_issue(
        Aspell $speller,
        $aspellDictionary,
        GlobalOffsetCalculator $globalOffsetCalculator
    ) {
        $text = 'Typos happen.';
        $locale = 'en_US';

        $aspellDictionary->getUpToDateLocalDictionaryRelativeFilePath(new LocaleCode($locale))->willReturn('a/relative/filepath.pws');

        $aspellCheckResult = [];

        $speller->checkText($text, [$locale])->willReturn($aspellCheckResult);

        $globalOffsetCalculator->compute(Argument::cetera())->shouldNotBeCalled();

        $result = $this->check($text, new LocaleCode($locale));

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }
}

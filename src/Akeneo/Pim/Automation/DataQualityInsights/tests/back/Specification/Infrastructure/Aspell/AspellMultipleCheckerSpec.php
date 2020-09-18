<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Result\AspellGlobalOffsetCalculator;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class AspellMultipleCheckerSpec extends ObjectBehavior
{
    public function let(
        AspellChecker $checker,
        AspellGlobalOffsetCalculator $globalOffsetCalculator,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith($checker, $globalOffsetCalculator, $logger);
    }

    public function it_checks_multiple_texts(
        AspellChecker $checker,
        AspellGlobalOffsetCalculator $globalOffsetCalculator
    ): void {
        $localeCode = new LocaleCode('en_US');
        $multipleTexts = [
            'text1' => 'typo hapen',
            'text2' => 'bad typoo',
            'text3' => 'valid typo',
        ];
        $expectedResults = [
            'text1' => $this->createSpellcheckResult([
                ['word' => 'hapen', 'globalOffset' => 5, 'offset' => 5, 'line' => 1, 'suggestions' => ['happen']],
            ]),
            'text2' => $this->createSpellcheckResult([
                ['word' => 'typoo', 'globalOffset' => 4, 'offset' => 4, 'line' => 1, 'suggestions' => ['typo']],
            ]),
            'text3' => $this->createSpellcheckResult([]),
        ];

        $assembledText = "typo hapen\n\nbad typoo\n\nvalid typo\n\n";
        $spellcheckResult = $this->createSpellcheckResult([
            ['word' => 'hapen', 'globalOffset' => 5, 'offset' => 5, 'line' => 1, 'suggestions' => ['happen']],
            ['word' => 'typoo', 'globalOffset' => 12, 'offset' => 4, 'line' => 3, 'suggestions' => ['typo']],
        ]);

        $checker->check($assembledText, $localeCode)->willReturn($spellcheckResult);
        $globalOffsetCalculator->compute('typo hapen', 1, 5)->willReturn(5);
        $globalOffsetCalculator->compute('bad typoo', 1, 4)->willReturn(4);

        $this->check($multipleTexts, $localeCode)->shouldBeLike($expectedResults);
    }

    private function createSpellcheckResult(array $issues): TextCheckResultCollection
    {
        $results = new TextCheckResultCollection();

        foreach ($issues as $issue) {
            $results->add(new TextCheckResult(
                $issue['word'],
                TextCheckResult::SPELLING_ISSUE_TYPE,
                $issue['globalOffset'],
                $issue['offset'],
                $issue['line'],
                $issue['suggestions'] ?? []
            ));
        }

        return $results;
    }
}

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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Source\TextSource;
use PhpSpellcheck\MisspellingInterface;
use PhpSpellcheck\Spellchecker\SpellcheckerInterface;

class TigitzSpeller implements SpellerInterface
{
    public function __construct(
        private SpellcheckerInterface           $speller,
        private AspellGlobalOffsetCalculator    $globalOffsetCalculator,
        private AspellLineNumberCalculator      $lineNumberCalculator,
        private TextCheckerDictionaryRepository $textCheckerDictionaryRepository,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function check(TextSource $source, LocaleCode $locale, array $context = []): TextCheckResultCollection
    {
        try {
            $issues = $this->speller->check($source->getAsString(), [(string)$locale], $context);
        } catch (\Throwable $throwable) {
            throw new TextCheckFailedException($throwable->getMessage());
        }

        return $this->adaptResult(
            array(...$issues),
            $source->getAsString(),
            $locale
        );
    }

    /**
     * @param array<MisspellingInterface> $issues
     * @throws TextCheckFailedException
     */
    private function adaptResult(array $issues, string $source, LocaleCode $localeCode): TextCheckResultCollection
    {
        $results = new TextCheckResultCollection();

        $ignoredWords = !empty($issues) ? $this->getUserIgnoredWords($localeCode, $issues) : [];

        foreach ($issues as $issue) {
            if (in_array(\mb_strtolower($issue->getWord()), $ignoredWords, false)) {
                continue;
            }

            $offset = $issue->getOffset();
            $line = $issue->getLineNumber();
            if (null === $offset || null === $line) {
                throw new TextCheckFailedException('A check text issue must have an offset and a line.');
            }

            $lineNumber = $this->lineNumberCalculator->compute($source, $line, $offset, $issue->getWord());
            $globalOffset = $this->globalOffsetCalculator->compute($source, $lineNumber, $offset);

            $results->add(new TextCheckResult(
                $issue->getWord(),
                TextCheckResult::SPELLING_ISSUE_TYPE,
                $globalOffset,
                $offset,
                $lineNumber,
                $issue->getSuggestions()
            ));
        }

        return $results;
    }

    /**
     * @param array<MisspellingInterface> $issues
     * @return array<string> List of words in user's dictionary
     */
    private function getUserIgnoredWords(LocaleCode $localeCode, array $issues): array
    {
        $wordsWithIssue = [];
        foreach ($issues as $issue) {
            try {
                $wordsWithIssue[] = new DictionaryWord($issue->getWord());
            } catch (\InvalidArgumentException $e) {
            }
        }

        $ignoredWords = $this->textCheckerDictionaryRepository->filterExistingWords($localeCode, $wordsWithIssue);

        return array_map('strval', $ignoredWords);
    }
}

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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Source\TextSource;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Mekras\Speller\Exception\PhpSpellerException;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellChecker implements TextChecker
{
    const DEFAULT_ENCODING = 'UTF-8';

    /** @var SpellerProviderInterface */
    private $spellerProvider;

    /** @var string */
    private $encoding;

    /** @var AspellGlobalOffsetCalculator */
    private $globalOffsetCalculator;

    private $textCheckerDictionaryRepository;

    private $lineNumberCalculator;

    public function __construct(
        SpellerProviderInterface $spellerProvider,
        AspellGlobalOffsetCalculator $globalOffsetCalculator,
        AspellLineNumberCalculator $lineNumberCalculator,
        TextCheckerDictionaryRepository $textCheckerDictionaryRepository,
        string $encoding = self::DEFAULT_ENCODING
    ) {
        $this->spellerProvider = $spellerProvider;
        $this->globalOffsetCalculator = $globalOffsetCalculator;
        $this->encoding = $encoding;
        $this->textCheckerDictionaryRepository = $textCheckerDictionaryRepository;
        $this->lineNumberCalculator = $lineNumberCalculator;
    }

    /**
     * @throws TextCheckFailedException
     */
    public function check(string $text, LocaleCode $localeCode): TextCheckResultCollection
    {
        try {
            $source = new TextSource($text);
        } catch (\Throwable $e) {
            throw new TextCheckFailedException($e->getMessage());
        }

        try {
            $speller = $this->spellerProvider->getByLocale($localeCode);
            return $this->adaptResult(
                $speller->checkText($source, [$localeCode->__toString()]),
                $source->getAsString(),
                $localeCode
            );
        } catch (PhpSpellerException $e) {
            throw new TextCheckFailedException($e->getMessage());
        }
    }

    private function adaptResult(array $issues, string $source, LocaleCode $localeCode): TextCheckResultCollection
    {
        $results = new TextCheckResultCollection();

        $ignoredWords = !empty($issues) ? $this->getUserIgnoredWords($localeCode, $issues) : [];

        foreach ($issues as $issue) {
            if (in_array($issue->word, $ignoredWords)) {
                continue;
            }

            if (null === $issue->offset || null === $issue->line) {
                throw new TextCheckFailedException('A check text issue must have an offset and a line.');
            }

            $offset = $issue->offset;
            $line = $issue->line;

            if (is_string($offset)) {
                $offset = (int) $offset;
            }

            if (is_string($line)) {
                $line = (int) $line;
            }

            $lineNumber = $this->lineNumberCalculator->compute($source, $line, $offset, $issue->word);
            $globalOffset = $this->globalOffsetCalculator->compute($source, $lineNumber, $offset);

            $results->add(new TextCheckResult(
                $issue->word,
                TextCheckResult::SPELLING_ISSUE_TYPE,
                $globalOffset,
                $offset,
                $lineNumber,
                $issue->suggestions
            ));
        }

        return $results;
    }

    private function getUserIgnoredWords(LocaleCode $localeCode, array $issues): array
    {
        $wordsWithIssue = [];
        foreach ($issues as $issue) {
            try {
                $wordsWithIssue[] = new DictionaryWord($issue->word);
            } catch (\InvalidArgumentException $e) {
            }
        }

        $ignoredWords = $this->textCheckerDictionaryRepository->filterExistingWords($localeCode, $wordsWithIssue);

        return array_map('strval', $ignoredWords);
    }
}

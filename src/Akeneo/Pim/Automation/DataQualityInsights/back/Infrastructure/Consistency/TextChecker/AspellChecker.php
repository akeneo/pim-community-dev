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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\TextSource;
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

    /** @var GlobalOffsetCalculator */
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

    public function check(string $text, LocaleCode $localeCode): TextCheckResultCollection
    {
        $source = new TextSource($text);

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

        $userGeneratedDictionary = $this->getUserGeneratedDictionary($localeCode);

        foreach ($issues as $issue) {
            if (in_array($issue->word, $userGeneratedDictionary)) {
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

    private function getUserGeneratedDictionary(LocaleCode $localeCode): array
    {
        $userGeneratedDictionary = [];

        $userGeneratedIgnoredWords = $this->textCheckerDictionaryRepository->findByLocaleCode($localeCode);

        foreach ($userGeneratedIgnoredWords as $textCheckerDictionaryWord) {
            $userGeneratedDictionary[] = strval($textCheckerDictionaryWord->getWord());
        }

        return $userGeneratedDictionary;
    }
}

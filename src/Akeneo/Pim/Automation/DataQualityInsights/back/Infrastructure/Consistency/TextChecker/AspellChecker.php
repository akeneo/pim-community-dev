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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\DictionaryNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Result\AspellGlobalOffsetCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Result\AspellLineNumberCalculator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\TextSource;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Dictionary;
use Mekras\Speller\Exception\PhpSpellerException;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellChecker implements TextChecker
{
    const DEFAULT_ENCODING = 'UTF-8';

    private $aspell;

    private $encoding;

    private $globalOffsetCalculator;

    private $aspellDictionary;

    private $textCheckerDictionaryRepository;

    private $lineNumberCalculator;

    public function __construct(string $binaryPath, AspellDictionaryInterface $aspellDictionary, AspellGlobalOffsetCalculator $globalOffsetCalculator, AspellLineNumberCalculator $lineNumberCalculator, TextCheckerDictionaryRepository $textCheckerDictionaryRepository, $encoding = self::DEFAULT_ENCODING)
    {
        $this->aspell = new Aspell($binaryPath);
        $this->globalOffsetCalculator = $globalOffsetCalculator;
        $this->encoding = $encoding;
        $this->aspellDictionary = $aspellDictionary;
        $this->textCheckerDictionaryRepository = $textCheckerDictionaryRepository;
        $this->lineNumberCalculator = $lineNumberCalculator;
    }

    public function check(string $text, LocaleCode $localeCode): TextCheckResultCollection
    {
        $source = new TextSource($text);

        try {
            $this->aspell->setPersonalDictionary($this->getDictionary($localeCode));
        } catch (DictionaryNotFoundException $e) {
            //No dictionary generated yet or no words in dictionary. Use spell checker without custom dictionary.
        }

        try {
            return $this->adaptResult(
                $this->aspell->checkText($source, [$localeCode->__toString()]),
                $source->getAsString(),
                $localeCode
            );
        } catch (PhpSpellerException $e) {
            return new TextCheckResultCollection();
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

    /**
     * @throws DictionaryNotFoundException
     */
    private function getDictionary(LocaleCode $localeCode): Dictionary
    {
        $absoluteDictionaryFilepath = $this->aspellDictionary->getUpToDateLocalDictionaryAbsoluteFilePath($localeCode);

        if (false === is_file($absoluteDictionaryFilepath)) {
            throw new DictionaryNotFoundException();
        }

        return new Dictionary($absoluteDictionaryFilepath);
    }
}

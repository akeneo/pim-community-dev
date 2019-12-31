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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\TextSource;
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

    /** @var AspellDictionary */
    private $aspellDictionary;

    /** @var string */
    private $dictionaryLocalFilesystemPath;

    public function __construct(string $binaryPath, AspellDictionary $aspellDictionary, string $dictionaryLocalFilesystemPath, $encoding = self::DEFAULT_ENCODING)
    {
        $this->aspell = new Aspell($binaryPath);
        $this->encoding = $encoding;
        $this->aspellDictionary = $aspellDictionary;
        $this->dictionaryLocalFilesystemPath = $dictionaryLocalFilesystemPath;
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
                $this->aspell->checkText($source, [$localeCode->__toString()])
            );
        } catch (PhpSpellerException $e) {
            return new TextCheckResultCollection();
        }
    }

    private function adaptResult(array $issues): TextCheckResultCollection
    {
        $results = new TextCheckResultCollection();

        foreach ($issues as $issue) {
            $offset = $issue->offset;
            $line = $issue->line;

            if (is_string($offset)) {
                $offset = (int) $offset;
            }

            if (is_string($line)) {
                $line = (int) $line;
            }

            $results->add(new TextCheckResult(
                $issue->word,
                $issue->code,
                $offset,
                $line,
                $issue->suggestions
            ));
        }

        return $results;
    }

    /**
     * @throws DictionaryNotFoundException
     */
    private function getDictionary(LocaleCode $localeCode): Dictionary
    {
        $relativeDictionaryFilepath = $this->aspellDictionary->getUpToDateLocalDictionaryRelativeFilePath($localeCode);
        $absoluteDictionaryFilepath = rtrim($this->dictionaryLocalFilesystemPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($relativeDictionaryFilepath, DIRECTORY_SEPARATOR);

        if (false === is_file($absoluteDictionaryFilepath)) {
            throw new DictionaryNotFoundException();
        }

        return new Dictionary($absoluteDictionaryFilepath);
    }
}

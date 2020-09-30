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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\MultipleTextsChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Result\AspellGlobalOffsetCalculator;
use Psr\Log\LoggerInterface;

class AspellMultipleChecker implements MultipleTextsChecker
{
    public const SEPARATOR = "\n\n";

    /** @var AspellChecker */
    private $checker;

    /** @var AspellGlobalOffsetCalculator */
    private $globalOffsetCalculator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AspellChecker $checker,
        AspellGlobalOffsetCalculator $globalOffsetCalculator,
        LoggerInterface $logger
    ) {
        $this->checker = $checker;
        $this->globalOffsetCalculator = $globalOffsetCalculator;
        $this->logger = $logger;
    }

    public function check(array $texts, LocaleCode $localeCode): array
    {
        if (empty($texts)) {
            return [];
        }

        list('mapping' => $mapping, 'text' => $text) = $this->assembleTexts($texts);

        $rawResults = $this->doCheck($text, $localeCode);

        return $this->recomposeResults($rawResults, $mapping);
    }

    private function doCheck(string $text, LocaleCode $localeCode): TextCheckResultCollection
    {
        try {
            $this->logger->debug('Multiple spellcheck: check spelling', [
                'text' => $text,
                'locale' => strval($localeCode),
            ]);
            return $this->checker->check($text, $localeCode);
        } catch (TextCheckFailedException $e) {
            $this->logger->error('Multiple spellcheck: check failed', [
                'text' => $text,
                'locale' => strval($localeCode),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function assembleTexts(array $texts): array
    {
        $assembledText = '';
        $mapping = [];
        $start = 1;

        foreach ($texts as $context => $text) {
            $assembledText .= $text . self::SEPARATOR;
            $end = $start + $this->countLines($text);

            $mapping[$context] = [
                'start' => $start,
                'end' => $end,
                'source' => $text,
            ];

            $start = $this->countLines($assembledText) + 1;
        }

        return [
            'mapping' => $mapping,
            'text' => $assembledText,
        ];
    }

    private function recomposeResults(TextCheckResultCollection $resultCollection, array $mapping): array
    {
        return array_map(function ($context) use ($resultCollection) {
            list('start' => $start, 'end' => $end, 'source' => $source) = $context;
            return $this->filterResultsByContext($resultCollection, $start, $end, $source);
        }, $mapping);
    }

    private function countLines(string $text): int
    {
        $lines = explode("\n", $text);

        return count($lines) - 1;
    }

    private function filterResultsByContext(TextCheckResultCollection $results, int $start, int $end, string $source): TextCheckResultCollection
    {
        $filteredResults = new TextCheckResultCollection();

        /** @var TextCheckResult $result */
        foreach ($results as $result) {
            if ($result->getLine() < $start || $result->getLine() > $end) {
                continue;
            }

            $offset = $result->getOffset();
            $line = $result->getLine() - $start + 1;
            $globalOffset = $this->globalOffsetCalculator->compute($source, $line, $offset);

            $filteredResults->add(new TextCheckResult(
                $result->getText(),
                $result->getType(),
                $globalOffset,
                $offset,
                $line,
                $result->getSuggestions()
            ));
        }

        return $filteredResults;
    }
}

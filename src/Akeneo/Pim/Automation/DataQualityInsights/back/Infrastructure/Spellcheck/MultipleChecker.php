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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\MultipleTextsChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Result\AspellGlobalOffsetCalculator;

class MultipleChecker implements MultipleTextsChecker
{
    public const SEPARATOR = "\n\n";

    public function __construct(
        private Checker                      $checker,
        private AspellGlobalOffsetCalculator $globalOffsetCalculator
    ) {
    }

    public function check(array $texts, LocaleCode $localeCode): array
    {
        if (empty($texts)) {
            return [];
        }

        list('mapping' => $mapping, 'text' => $text) = $this->assembleTexts($texts);

        $rawResults = $this->checker->check($text, $localeCode);

        return $this->recomposeResults($rawResults, $mapping);
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

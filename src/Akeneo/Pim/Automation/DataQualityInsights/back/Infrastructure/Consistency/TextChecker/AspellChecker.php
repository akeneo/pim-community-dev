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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source\TextSource;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Exception\PhpSpellerException;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellChecker implements TextChecker
{
    const DEFAULT_ENCODING = 'UTF-8';

    private $aspell;

    private $encoding;

    public function __construct(string $binaryPath, $encoding = self::DEFAULT_ENCODING)
    {
        $this->aspell = new Aspell($binaryPath);
        $this->encoding = $encoding;
    }

    public function check(string $text, string $locale): TextCheckResultCollection
    {
        $source = new TextSource($text);

        // @todo[DAPI-601] handle "personal" dictionary
        // $aspell->setPersonalDictionary(new Dictionary(__DIR__ . '/fixtures/custom.en.pws'));

        try {
            return $this->adaptResult(
                $this->aspell->checkText($source, [$locale])
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
}

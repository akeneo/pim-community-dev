<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

final class ProductValueInDatabaseDictionarySource implements DictionarySource
{
    private const RELEVANT_NUMBER_OF_OCCURRENCE = 10;
    private const RELEVANT_NUMBER_OF_LETTER_IN_A_WORD = 4;

    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getDictionary(LocaleCollection $localeCollection): Dictionary
    {
        $words = $this->extractRelevantWordsFromProductValues($localeCollection);

        return new Dictionary($words);
    }

    private function extractRelevantWordsFromProductValues(LocaleCollection $localeCollection): array
    {
        $keywords = [];

        $stmt = $this->getQuery($localeCollection);

        while ($result = $stmt->fetch()) {
            $explodedAggregatedValues = json_decode($result['explodedAggregatedValues']);
            foreach ($explodedAggregatedValues as $value) {
                if (empty($value) || !is_string($value) || filter_var($value, FILTER_VALIDATE_INT)) {
                    continue;
                }

                $aWordPerLine = wordwrap(strip_tags(trim($value)), 1);
                $arrayOfWords = explode(PHP_EOL, $aWordPerLine);

                $filteredArrayOfWords = array_filter($arrayOfWords, function ($word) {
                    $word = rtrim($word, '.,:');
                    preg_match("~^[a-zA-Z]+$~", $word, $authorizedCharactersOnly);
                    return mb_strlen($word) >= self::RELEVANT_NUMBER_OF_LETTER_IN_A_WORD
                        && strpos($word, 'http') !== 0
                        && !filter_var($word, FILTER_VALIDATE_FLOAT)
                        && !filter_var($word, FILTER_VALIDATE_INT)
                        && count($authorizedCharactersOnly) === 1
                        ;
                });

                if (empty($filteredArrayOfWords)) {
                    continue;
                }

                foreach ($filteredArrayOfWords as $word) {
                    $word = trim($word);
                    $word = rtrim($word, '.,:');
                    if (!array_key_exists($word, $keywords)) {
                        $keywords[$word] = 1;
                        continue;
                    }
                    $keywords[$word] += 1;
                }
            }
        }

        $tab = array_filter($keywords, function ($count) {
            return $count > self::RELEVANT_NUMBER_OF_OCCURRENCE;
        });
        asort($tab);

        return array_keys($tab);
    }

    private function getQuery(LocaleCollection $localeCollection): ResultStatement
    {
        $this->db->getWrappedConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $query = <<<SQL
SELECT
    %s AS explodedAggregatedValues
FROM pim_catalog_product
SQL;

        $query = sprintf(
            $query,
            $this->generateJsonExtractQueryPart($localeCollection)
        );

        return $this->db->query($query);
    }

    private function generateJsonExtractQueryPart(LocaleCollection $localeCollection)
    {
        $allLocalesQueryPart = 'IFNULL(raw_values->\'$.*.*."<all_locales>"\', \'[]\')';

        $jsonExtractQueryPart = [$allLocalesQueryPart];

        foreach ($localeCollection as $localeCode) {
            $jsonExtractQueryPart[] = sprintf(
                'IFNULL(raw_values->\'$.*.*.%s\', \'[]\')',
                $localeCode->__toString()
            );
        }

        if (count($jsonExtractQueryPart) === 1) {
            return $allLocalesQueryPart;
        }

        return sprintf(
            'json_merge_preserve(%s)',
            implode(', ', $jsonExtractQueryPart)
        );
    }
}

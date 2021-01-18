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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\TextCheckerDictionaryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextCheckerDictionaryRepository implements TextCheckerDictionaryRepositoryInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return Read\TextCheckerDictionaryWord[] array
     */
    public function findByLocaleCode(LocaleCode $localeCode): array
    {
        $query = <<<SQL
SELECT locale_code, word
FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code = :localeCode
SQL;

        $statement = $this->db->executeQuery($query, [
            'localeCode' => strval($localeCode),
        ]);
        return array_map(function ($row) {
            return new Read\TextCheckerDictionaryWord(
                new LocaleCode($row['locale_code']),
                new DictionaryWord($row['word'])
            );
        }, $statement->fetchAll(FetchMode::ASSOCIATIVE));
    }

    public function exists(LocaleCode $localeCode, DictionaryWord $word): bool
    {
        $query = <<<SQL
SELECT word
FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code = :localeCode AND word = :word
SQL;

        $statement = $this->db->executeQuery($query, [
            'localeCode' => strval($localeCode),
            'word' => strval($word),
        ]);

        $results = $statement->fetchAll(FetchMode::ASSOCIATIVE);

        if (empty($results)) {
            return false;
        }

        foreach ($results as $result) {
            if ($result['word'] === strval($word)) {
                return true;
            }
        }

        return false;
    }

    public function save(Write\TextCheckerDictionaryWord $dictionaryWord): void
    {
        $query = <<<SQL
INSERT IGNORE INTO  pimee_data_quality_insights_text_checker_dictionary (locale_code, word)
VALUES (:localeCode, :word)
SQL;

        $this->db->executeUpdate($query,
            [
                'localeCode' => strval($dictionaryWord->getLocaleCode()),
                'word' => strtolower(strval($dictionaryWord->getWord())),
            ],
            [
                'localeCode' => \PDO::PARAM_STR,
                'word' => \PDO::PARAM_STR,
            ]
        );
    }

    public function paginatedSearch(LocaleCode $localeCode, int $page, int $itemsPerPage, string $search): array
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('count(word) as nb_results')
            ->from('pimee_data_quality_insights_text_checker_dictionary')
            ->where(
                $qb->expr()->eq(
                    'locale_code',
                    $qb->createPositionalParameter(strval($localeCode), ParameterType::STRING)
                )
            );

        if (!empty($search)) {
            $search = '%' . $search . '%';
            $qb->andWhere(
                $qb->expr()->like(
                    'word',
                    $qb->createPositionalParameter($search, ParameterType::STRING)
                )
            );
        }

        $totalNumberOfWords = $qb->execute()->fetchColumn();

        $qb->select('id, word as label')
            ->orderBy('word', 'ASC')
            ->setFirstResult(($page-1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $words = $qb->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        return [
            'results' => $words,
            'total' => intval($totalNumberOfWords),
        ];
    }

    public function deleteWord(int $wordId): void
    {
        $this->db->delete('pimee_data_quality_insights_text_checker_dictionary', ['id' => $wordId]);
    }
}

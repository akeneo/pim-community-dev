<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary\GetNumberOfWordsQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class GetNumberOfWordsQuery implements GetNumberOfWordsQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byLocales(array $locales): array
    {
        $query = <<<SQL
SELECT locale_code, COUNT(word) AS total_words
FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code IN (:locales)
GROUP BY locale_code;
SQL;

        $stmt = $this->connection->executeQuery(
            $query,
            ['locales' => $locales],
            ['locales' => Connection::PARAM_STR_ARRAY]
        );

        $wordsByLocales = [];
        while ($localeWords = $stmt->fetch(FetchMode::ASSOCIATIVE)) {
            $wordsByLocales[$localeWords['locale_code']] = intval($localeWords['total_words']);
        }

        return $wordsByLocales;
    }
}

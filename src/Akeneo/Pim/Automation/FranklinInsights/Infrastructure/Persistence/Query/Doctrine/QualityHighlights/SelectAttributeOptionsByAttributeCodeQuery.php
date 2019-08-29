<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeOptionsByAttributeCodeQueryInterface;
use Doctrine\DBAL\Connection;

class SelectAttributeOptionsByAttributeCodeQuery implements SelectAttributeOptionsByAttributeCodeQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $attributeCode): array
    {
        $sql = <<<'SQL'
            SELECT 
                `option`.code,
                (SELECT JSON_OBJECTAGG(IFNULL(locale_code, 0), value) FROM pim_catalog_attribute_option_value WHERE option_id = option.id) AS labels
            FROM pim_catalog_attribute_option `option`
            INNER JOIN pim_catalog_attribute attribute ON attribute.id = `option`.attribute_id
            WHERE attribute.code = :attribute_code;
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            ['attribute_code' => $attributeCode],
            ['attribute_code' => \PDO::PARAM_STR]
        );

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function (array $row) {
            return [
                'code' => $row['code'],
                'labels' => $this->buildLabelsFromJsonString($row['labels']),
            ];
        }, $results);
    }

    private function buildLabelsFromJsonString(?string $labelsJson): array
    {
        $labels = [];

        if (! empty($labelsJson)) {
            $translations = json_decode($labelsJson, true);
            $labels = array_map(function ($label, $locale) {
                return [
                    'locale' => $locale,
                    'label' => $label,
                ];
            }, $translations, array_keys($translations));
        }

        return $labels;
    }
}

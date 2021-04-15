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

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Query\Sql;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetAttributesRulesNumber;
use Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver;
use Doctrine\DBAL\Connection;

final class SqlGetAttributesRulesNumber implements GetAttributesRulesNumber
{
    private Connection $connection;

    private FQCNResolver $FQCNResolver;

    public function __construct(Connection $connection, FQCNResolver $FQCNResolver)
    {
        $this->connection = $connection;
        $this->FQCNResolver = $FQCNResolver;
    }

    public function execute(array $attributeCodes): array
    {
        $query = <<<SQL
SELECT attribute.code AS attribute_code, COUNT(*) AS attribute_rules_number
FROM akeneo_rule_engine_rule_relation rule_relation
INNER JOIN pim_catalog_attribute attribute ON rule_relation.resource_id = attribute.id
WHERE resource_name = :resource_name
AND attribute.code IN(:attribute_codes)
GROUP BY attribute.code
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            [
                'attribute_codes' => $attributeCodes,
                'resource_name' => $this->FQCNResolver->getFQCN('attribute')
            ],
            [
                'attribute_codes' => Connection::PARAM_STR_ARRAY,
                'resource_name' => \PDO::PARAM_STR,
            ]
        )->fetchAll();

        $result = [];
        foreach ($rows as $rule) {
            $result[$rule['attribute_code']] = $rule['attribute_rules_number'];
        }

        return $result;
    }
}

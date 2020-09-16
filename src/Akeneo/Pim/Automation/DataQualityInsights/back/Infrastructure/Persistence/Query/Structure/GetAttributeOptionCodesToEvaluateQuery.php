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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionCodesToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class GetAttributeOptionCodesToEvaluateQuery implements GetAttributeOptionCodesToEvaluateQueryInterface
{
    // Arbitrary limit to not have a too big query. It would need refactoring to remove it.
    private const MAX_UPDATED_OPTION_IDS = 10000;

    /** @var Connection */
    private $dbConnection;

    /** @var string */
    private $attributeOptionClass;

    public function __construct(Connection $dbConnection, string $attributeOptionClass)
    {
        $this->dbConnection = $dbConnection;
        $this->attributeOptionClass = $attributeOptionClass;
    }

    public function execute(\DateTimeImmutable $updatedSince): iterable
    {
        $updatedOptionsIds = $this->getUpdatedAttributeOptionIds($updatedSince);

        $query = <<<SQL
SELECT attribute.code AS attribute_code, attribute_option.code AS attribute_option_code
FROM pim_catalog_attribute_option AS attribute_option
INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = attribute_option.attribute_id
LEFT JOIN pimee_dqi_attribute_option_spellcheck AS spellcheck
    ON spellcheck.attribute_code = attribute.code AND spellcheck.attribute_option_code = attribute_option.code
WHERE spellcheck.evaluated_at IS NULL OR attribute_option.id IN (:updatedOptionsIds)

SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['updatedOptionsIds' => $updatedOptionsIds],
            ['updatedOptionsIds' => Connection::PARAM_INT_ARRAY]
        );

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield new AttributeOptionCode(new AttributeCode($row['attribute_code']), $row['attribute_option_code']);
        }
    }

    private function getUpdatedAttributeOptionIds(\DateTimeImmutable $updatedSince): array
    {
        $query = <<<SQL
SELECT DISTINCT versioning.resource_id
FROM pim_versioning_version AS versioning
WHERE versioning.resource_name = :resourceName
    AND versioning.logged_at > :updatedSince
LIMIT :limit
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            [
                'resourceName' => $this->attributeOptionClass,
                'updatedSince' => $updatedSince->format(Clock::TIME_FORMAT),
                'limit' => self::MAX_UPDATED_OPTION_IDS
            ],
            [
                'limit' => \PDO::PARAM_INT,
            ]
        );

        return $stmt->fetchAll(FetchMode::COLUMN);
    }
}

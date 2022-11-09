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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributesCodesToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Doctrine\DBAL\Connection;

class GetAttributesCodesToEvaluateQuery implements GetAttributesCodesToEvaluateQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(): iterable
    {
        $query = <<<SQL
SELECT attribute.code
FROM pim_catalog_attribute AS attribute
LEFT JOIN pimee_dqi_attribute_spellcheck AS spellcheck ON spellcheck.attribute_code = attribute.code
WHERE spellcheck.attribute_code IS NULL OR spellcheck.evaluated_at < attribute.updated
SQL;

        $stmt = $this->dbConnection->executeQuery($query);

        while ($attributeCode = $stmt->fetchColumn()) {
            yield new AttributeCode($attributeCode);
        }
    }

    /**
     * Get attribute codes flagged "to improve" but needs to be reevaluated
     * because all options and label translations are flagged "good".
     *
     * @return iterable
     * @throws \Doctrine\DBAL\Exception
     */
    public function toReevaluate(): iterable
    {
        $query = <<<SQL
WITH attribute_option_good AS (
    SELECT attr_option_spellcheck.attribute_code, count(attr_option_spellcheck.attribute_code) AS totalAttributeOptionGood
    FROM pimee_dqi_attribute_quality AS attr_quality
    INNER JOIN pimee_dqi_attribute_option_spellcheck AS attr_option_spellcheck ON attr_option_spellcheck.attribute_code = attr_quality.attribute_code
    WHERE attr_option_spellcheck.to_improve = 0
    GROUP BY attr_quality.attribute_code
)
SELECT attr.code, count(attr.id) AS totalAttributeOption, attribute_option_good.totalAttributeOptionGood
FROM pim_catalog_attribute AS attr
INNER JOIN pim_catalog_attribute_option AS ao ON ao.attribute_id = attr.id
INNER JOIN pimee_dqi_attribute_quality AS attr_quality ON attr_quality.attribute_code = attr.code
INNER JOIN pimee_dqi_attribute_spellcheck AS attr_spellcheck ON attr_spellcheck.attribute_code = attr_quality.attribute_code
INNER JOIN attribute_option_good ON attribute_option_good.attribute_code = attr_quality.attribute_code
WHERE attr_quality.quality = :to_improve
AND attr_spellcheck.to_improve = 0
GROUP BY attr.code
HAVING totalAttributeOption = attribute_option_good.totalAttributeOptionGood;
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['to_improve' => Quality::TO_IMPROVE]);

        while ($result = $stmt->fetchOne()) {
            yield new AttributeCode($result);
        }
    }
}

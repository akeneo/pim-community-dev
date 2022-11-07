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

        while ($attributeCode = $stmt->fetchOne()) {
            yield new AttributeCode($attributeCode);
        }
    }
}

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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Doctrine\DBAL\Connection;

final class GetAttributeQuery implements GetAttributeQueryInterface
{
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @warn
     * This query is only used in the GetNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes service.
     * We do not need to know if the attribute is defined as main title.
     */
    public function byAttributeCode(AttributeCode $attributeCode): ?Attribute
    {
        $query = <<<SQL
SELECT attribute_type, is_localizable
FROM pim_catalog_attribute attribute
WHERE attribute.code = :attribute_code
SQL;
        $statement = $this->dbConnection->executeQuery(
            $query,
            ['attribute_code' => strval($attributeCode)],
            ['attribute_code' => \PDO::PARAM_STR]
        );

        $attribute = $statement->fetch(\PDO::FETCH_ASSOC);

        if (empty($attribute)) {
            return null;
        }

        return new Attribute(
            $attributeCode,
            new AttributeType($attribute['attribute_type']),
            (bool) $attribute['is_localizable']
        );
    }
}

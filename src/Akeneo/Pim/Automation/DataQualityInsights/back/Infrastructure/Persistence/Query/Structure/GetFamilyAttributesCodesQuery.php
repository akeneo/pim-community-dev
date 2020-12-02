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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamilyAttributesCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Doctrine\DBAL\Connection;

final class GetFamilyAttributesCodesQuery implements GetFamilyAttributesCodesQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byFamilyId(FamilyId $familyId): array
    {
        $query = <<<SQL
SELECT attribute.code 
FROM pim_catalog_family_attribute AS family_attribute 
INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = family_attribute.attribute_id
WHERE family_attribute.family_id = :familyId
SQL;

        $attributesCodes = $this->dbConnection->executeQuery(
            $query,
            ['familyId' => $familyId->toInt()],
            ['familyId' => \PDO::PARAM_INT]
        )->fetchAll(\PDO::FETCH_COLUMN);

        return array_map(fn ($attributeCode) => new AttributeCode($attributeCode), $attributesCodes);
    }
}

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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionLabelsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Doctrine\DBAL\Connection;

class GetAttributeOptionLabelsQuery implements GetAttributeOptionLabelsQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byCode(AttributeOptionCode $attributeOptionCode): array
    {
        $query = <<<SQL
SELECT JSON_OBJECTAGG(option_value.locale_code, option_value.value) AS attribute_option_labels
FROM pim_catalog_attribute_option_value AS option_value
INNER JOIN pim_catalog_attribute_option AS attribute_option ON attribute_option.id = option_value.option_id
INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = attribute_option.attribute_id
WHERE attribute.code = :attributeCode AND attribute_option.code = :optionCode
SQL;

        $attributeOptionLabels = $this->dbConnection->executeQuery($query, [
            'attributeCode' => $attributeOptionCode->getAttributeCode(),
            'optionCode' => $attributeOptionCode
        ])->fetchOne();

        return is_string($attributeOptionLabels) ? json_decode($attributeOptionLabels, true) : [];
    }
}

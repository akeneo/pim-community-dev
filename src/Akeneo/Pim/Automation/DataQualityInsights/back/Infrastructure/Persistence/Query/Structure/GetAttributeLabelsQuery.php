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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeLabelsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Doctrine\DBAL\Connection;

class GetAttributeLabelsQuery implements GetAttributeLabelsQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byCode(AttributeCode $attributeCode)
    {
        $query = <<<SQL
SELECT JSON_OBJECTAGG(attribute_labels.locale, attribute_labels.label) AS labels
FROM pim_catalog_attribute_translation AS attribute_labels
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = attribute_labels.foreign_key
    INNER JOIN pim_catalog_locale AS locale ON locale.code = attribute_labels.locale AND locale.is_activated = 1
WHERE attribute.code = :attributeCode
SQL;

        $rawLabels = $this->dbConnection->executeQuery($query, ['attributeCode' => $attributeCode])->fetchColumn();

        return is_string($rawLabels) ? json_decode($rawLabels, true) : [];
    }
}

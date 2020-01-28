<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectAttributeLabelsFromCodesQueryInterface;
use Doctrine\DBAL\Connection;

final class SelectAttributeLabelsFromCodesQuery implements SelectAttributeLabelsFromCodesQueryInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function execute(array $attributeCodes): array
    {
        $sql = <<<SQL
SELECT attribute.code, JSON_OBJECTAGG(attribute_translation.locale, attribute_translation.label) AS labels
FROM pim_catalog_attribute AS attribute
INNER JOIN pim_catalog_attribute_translation attribute_translation on attribute.id = attribute_translation.foreign_key
WHERE attribute.code IN(:attribute_codes)
GROUP BY attribute.code
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['attribute_codes' => $attributeCodes],
            ['attribute_codes' => Connection::PARAM_STR_ARRAY],
        );

        $attributeLabels = array_fill_keys($attributeCodes, []);
        foreach ($statement->fetchAll() as $resultRow) {
            $attributeLabels[$resultRow['code']] = json_decode($resultRow['labels'], true, JSON_THROW_ON_ERROR);
        }

        return $attributeLabels;
    }
}

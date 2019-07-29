<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCompletenessFamilyMasks
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $familyCodes
     *
     * @return CompletenessFamilyMask[]
     */
    public function fromFamilyCodes(array $familyCodes): array
    {
        $sql = <<<SQL
SELECT 
    familyCode,
    JSON_OBJECTAGG(channelCode, attributeCodes) AS mask
FROM (
    SELECT 
        family.code AS familyCode,
        channel.code AS channelCode,
        JSON_ARRAYAGG(attribute.code) AS attributeCodes
    FROM pim_catalog_attribute_requirement requirement
    INNER JOIN pim_catalog_attribute attribute ON attribute.id=requirement.attribute_id
    INNER JOIN pim_catalog_channel channel ON channel.id=requirement.channel_id
    INNER JOIN pim_catalog_family family ON family.id=requirement.family_id
    WHERE requirement.required=1
        AND family.code IN (:familyCodes)
    GROUP BY family.code, channel.code
) masks
GROUP BY familyCode
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['familyCodes' => $familyCodes],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['familyCode']] = new CompletenessFamilyMask(
                $row['familyCode'],
                json_decode($row['mask'], true)
            );
        }

        return $result;
    }
}

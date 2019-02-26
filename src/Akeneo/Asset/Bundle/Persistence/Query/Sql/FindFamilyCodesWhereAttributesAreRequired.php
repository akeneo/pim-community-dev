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

namespace Akeneo\Asset\Bundle\Persistence\Query\Sql;

use Akeneo\Asset\Component\Persistence\Query\Sql\FindFamilyCodesWhereAttributesAreRequiredInterface;
use Doctrine\DBAL\Connection;

/**
 * It finds families codes where given attributes codes are required.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FindFamilyCodesWhereAttributesAreRequired implements FindFamilyCodesWhereAttributesAreRequiredInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $query = <<< SQL
    SELECT DISTINCT family.code AS family_code
    FROM pim_catalog_family AS family
    LEFT JOIN pim_catalog_attribute_requirement AS requirement ON requirement.family_id = family.id
    LEFT JOIN pim_catalog_attribute AS attribute ON attribute.id = requirement.attribute_id
    WHERE attribute.code IN (:attribute_codes) and requirement.required = 1
SQL;
        $statement = $this->connection->executeQuery(
            $query,
            ['attribute_codes' => $attributeCodes],
            ['attribute_codes' => Connection::PARAM_STR_ARRAY]
        );

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}

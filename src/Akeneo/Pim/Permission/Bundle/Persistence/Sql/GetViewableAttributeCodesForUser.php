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

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Doctrine\DBAL\Connection;

/**
 * Filters a list of attribute by code based on whether the given user can view them
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetViewableAttributeCodesForUser implements GetViewableAttributeCodesForUserInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function forAttributeCodes(array $attributeCodes, int $userId): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $query = <<<SQL
        SELECT attribute.code
        FROM pim_catalog_attribute attribute
        WHERE EXISTS (
            SELECT *
            FROM pimee_security_attribute_group_access attribute_access
            INNER JOIN oro_user_access_group user_access_group on attribute_access.user_group_id = user_access_group.group_id AND user_access_group.user_id = :userId
            WHERE attribute_access.attribute_group_id = attribute.group_id
        )
        AND attribute.code IN (:attributeCodes)
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'userId' => $userId,
            'attributeCodes' => $attributeCodes
        ], ['attributeCodes' => Connection::PARAM_STR_ARRAY]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN, 'code');
    }
}

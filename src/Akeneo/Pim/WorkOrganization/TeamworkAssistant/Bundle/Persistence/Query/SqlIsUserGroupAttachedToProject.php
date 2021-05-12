<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserGroupAttachedToProject;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class SqlIsUserGroupAttachedToProject implements IsUserGroupAttachedToProject
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forUserGroupId(int $userGroupId): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1 FROM pimee_teamwork_assistant_project_user_group WHERE user_group_id = :userGroupId
        ) as is_existing
        SQL;

        $statement = $this->connection->executeQuery($query, ['userGroupId' => $userGroupId]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}

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

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\DeleteProjectStatusIfUserIsNotLinkedToProject;
use Doctrine\DBAL\Connection;

/**
 * Delete project status db rows if a user is not linked to any project.
 */
class DeleteProjectStatusDbRowsIfUserIsNotLinkedToProject implements DeleteProjectStatusIfUserIsNotLinkedToProject
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(int $userId): void
    {
        $sql = <<<SQL
DELETE FROM pimee_teamwork_assistant_project_status
WHERE user_id = :userId
AND
(
    SELECT COUNT(project_id)
    FROM oro_user_access_group uag
    INNER JOIN pimee_teamwork_assistant_project_user_group pug ON pug.user_group_id = uag.group_id
    INNER JOIN oro_access_group ag on uag.group_id = ag.id
    WHERE uag.user_id = :userId
    AND ag.name != :groupAll
) = 0
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('groupAll', 'All', \PDO::PARAM_STR);
        $stmt->execute();
    }
}

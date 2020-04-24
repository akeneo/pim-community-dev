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

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserLinkedToProjectsQueryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserOwnerOfProjectsQueryInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;

/**
 * @author jmleroux <jean-marie.leroux@akeneo.com>
 */
class IsUserOwnerOfProjectsQuery implements IsUserOwnerOfProjectsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(int $userId): bool
    {
        $sql = "SELECT EXISTS (SELECT * FROM pimee_teamwork_assistant_project project WHERE owner_id = :userId)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return ((int) $stmt->fetchColumn(0)) > 0;
    }
}

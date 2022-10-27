<?php

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\GetProjectCodeInterface;
use Doctrine\DBAL\Connection;

class GetProjectCode implements GetProjectCodeInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchAll(): array
    {
        $query = 'SELECT code FROM pimee_teamwork_assistant_project';

        $statement = $this->connection->executeQuery($query);

        return $statement->fetchFirstColumn();
    }
}

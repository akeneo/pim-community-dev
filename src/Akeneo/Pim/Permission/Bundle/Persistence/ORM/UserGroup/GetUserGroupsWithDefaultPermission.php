<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\UserGroup;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class GetUserGroupsWithDefaultPermission
{
    private Connection $connection;
    private Registry $doctrine;

    public function __construct(
        Connection $connection,
        Registry $doctrine
    ) {
        $this->connection = $connection;
        $this->doctrine = $doctrine;
    }

    /**
     * @return GroupInterface[]
     */
    public function execute(string $permission): array
    {
        $query = <<<SQL
SELECT id
FROM oro_access_group
WHERE default_permissions IS NOT NULL
AND JSON_EXTRACT(default_permissions, :path) = TRUE
SQL;

        $results = $this->connection->fetchAssoc($query, [
            'path' => sprintf('$.%s', $permission),
        ]) ?: [];

        $em = $this->doctrine->getManager();
        if (!$em instanceof EntityManagerInterface) {
            throw new \LogicException(sprintf('Expected %s, got %s', EntityManagerInterface::class, get_class($em)));
        }

        return array_map(function (string $id) use ($em) {
            return $em->getReference(Group::class, (int) $id);
        }, $results);
    }
}

<?php

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Loader;

use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;

class PermissionCleaner
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * Remove the group All after an import
     *
     * @throws \Exception
     */
    public function cleanPermission()
    {
        $sql = <<<SQL
            DELETE FROM pimee_security_attribute_group_access WHERE user_group_id IN (
                SELECT id FROM oro_access_group WHERE name = :group_name
            );
        SQL;

        $this->connection->executeQuery($sql, ['group_name' => User::GROUP_DEFAULT]);

        $sql = <<<SQL
            DELETE FROM pimee_security_product_category_access WHERE user_group_id IN (
                SELECT id FROM oro_access_group WHERE name = :group_name
            );
        SQL;

        $this->connection->executeQuery($sql, ['group_name' => User::GROUP_DEFAULT]);

        $sql = <<<SQL
            DELETE FROM pimee_security_locale_access WHERE user_group_id IN (
                SELECT id FROM oro_access_group WHERE name = :group_name
            );
        SQL;

        $this->connection->executeQuery($sql, ['group_name' => User::GROUP_DEFAULT]);
    }
}

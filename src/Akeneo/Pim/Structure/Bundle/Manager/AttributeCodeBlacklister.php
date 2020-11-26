<?php

namespace Akeneo\Pim\Structure\Bundle\Manager;

use Doctrine\DBAL\Connection;

class AttributeCodeBlacklister
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function blacklist(string $attributeCode)
    {
        $blacklistAttributeCode = <<<SQL
INSERT INTO `pim_catalog_attribute_blacklist` (`attribute_code`)
VALUES
    (:attribute_code);
SQL;

        $this->connection->executeUpdate(
            $blacklistAttributeCode,
            [
                ':attribute_code' => $attributeCode
            ]
        );
    }
}

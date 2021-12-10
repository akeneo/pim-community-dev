<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup;

use Akeneo\Pim\Permission\Component\Attributes;
use Doctrine\DBAL\Connection;

class GetAttributeGroupsAccessesWithHighestLevel
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @return array<string, string>
     *
     * @throws \LogicException
     */
    public function execute(int $groupId): array
    {
        $query = <<<SQL
SELECT pim_catalog_attribute_group.code, view_attributes AS view, edit_attributes AS edit
FROM pimee_security_attribute_group_access
JOIN pim_catalog_attribute_group ON pim_catalog_attribute_group.id = pimee_security_attribute_group_access.attribute_group_id
WHERE user_group_id = :user_group_id
SQL;

        $rows = $this->connection->fetchAllAssociative($query, [
            'user_group_id' => $groupId,
        ]) ?: [];

        $results = [];
        foreach ($rows as $row) {
            $results[$row['code']] = $this->getHighestAccessLevel($row);
        }

        return $results;
    }

    /**
     * @param array{edit: bool, view: bool} $row
     * @return string|null
     *
     * @throws \LogicException
     */
    private function getHighestAccessLevel(array $row): ?string
    {
        if ($row['edit']) {
            return Attributes::EDIT_ATTRIBUTES;
        } elseif ($row['view']) {
            return Attributes::VIEW_ATTRIBUTES;
        }

        throw new \LogicException('Attribute group access level is unknown');
    }
}

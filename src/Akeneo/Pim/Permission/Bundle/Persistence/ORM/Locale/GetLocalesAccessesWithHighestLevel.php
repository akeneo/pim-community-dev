<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale;

use Akeneo\Pim\Permission\Component\Attributes;
use Doctrine\DBAL\Connection;

class GetLocalesAccessesWithHighestLevel
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    )
    {
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
SELECT pim_catalog_locale.code,
       pimee_security_locale_access.view_products as view,
       pimee_security_locale_access.edit_products as edit
FROM pim_catalog_locale
JOIN pimee_security_locale_access on pim_catalog_locale.id = pimee_security_locale_access.locale_id
WHERE pimee_security_locale_access.user_group_id = :user_group_id
SQL;

        $rows = $this->connection->fetchAll($query, [
            'user_group_id' => $groupId,
        ]) ?: [];

        $results = [];
        foreach ($rows as $row) {
            $results[$row['code']] = $this->getHighestAccessLevel($row);
        }

        return $results;
    }

    /**
     * @param array{
     *     edit: bool,
     *     view: bool
     * } $row
     *
     * @throws \LogicException
     */
    private function getHighestAccessLevel(array $row): ?string
    {
        if ($row['edit']) {
            return Attributes::EDIT_ITEMS;
        } elseif ($row['view']) {
            return Attributes::VIEW_ITEMS;
        }

        throw new \LogicException('Category access level is unknown');
    }
}

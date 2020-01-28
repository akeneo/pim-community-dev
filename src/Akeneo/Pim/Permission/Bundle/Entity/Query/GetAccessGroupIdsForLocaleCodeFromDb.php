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

namespace Akeneo\Pim\Permission\Bundle\Entity\Query;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Query\GetAccessGroupIdsForLocaleCode;
use Doctrine\DBAL\Connection;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
class GetAccessGroupIdsForLocaleCodeFromDb implements GetAccessGroupIdsForLocaleCode
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get group ids that have the specified access to a locale code.
     *
     * @param string $localeCode
     * @param string $accessLevel
     *
     * @return int[]
     */
    public function getGrantedUserGroupIdsForLocaleCode(string $localeCode, string $accessLevel): array
    {
        $accessField = $this->getAccessField($accessLevel);
        $sql = <<<SQL
SELECT DISTINCT
    access_group.id
FROM pimee_security_locale_access locale_access
    JOIN pim_catalog_locale locale ON locale.id = locale_access.locale_id
    JOIN oro_access_group access_group ON access_group.id = locale_access.user_group_id
WHERE locale.code = :localeCode AND locale_access.{permission}
SQL;

        $sql = str_replace('{permission}', $accessField, $sql);

        return array_column(
            $this->connection->executeQuery(
                $sql,
                ['localeCode' => $localeCode],
            )->fetchAll(),
            'id'
        );
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @throws \LogicException
     *
     * @return string
     */
    private function getAccessField($accessLevel)
    {
        $mapping = [
            Attributes::EDIT_ITEMS => 'edit_products',
            Attributes::VIEW_ITEMS => 'view_products',
        ];
        if (!isset($mapping[$accessLevel])) {
            throw new \LogicException(sprintf('%s access level does not exist', $accessLevel));
        }

        return $mapping[$accessLevel];
    }
}

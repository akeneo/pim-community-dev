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

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql\LocaleRight;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Permission\Component\Query\IsValueViewableByUserForLocaleInterface;
use Doctrine\DBAL\Connection;

/**
 * Returns true or false if a user is able to view the locale of a given value
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsValueViewableByUserForLocale implements IsValueViewableByUserForLocaleInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var array */
    private $cache;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function isViewable(ValueInterface $value, int $userId): bool
    {
        $cacheKey = sprintf('%s_%s', $value->getLocaleCode(), $userId);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $localeCode = $value->getLocaleCode();

        if (null === $localeCode) {
            return true;
        }

        $query = <<<SQL
            SELECT EXISTS (
                SELECT
                    locale.code
                FROM
                    pimee_security_locale_access locale_access
                    JOIN pim_catalog_locale locale ON locale.id = locale_access.locale_id
                    JOIN oro_user_access_group user_access_group ON user_access_group.group_id = locale_access.user_group_id
                WHERE
                    locale.code = :localeCode
                    AND user_access_group.user_id = :userId
            )
SQL;

        $result = (bool) ($this->sqlConnection->fetchColumn($query, [
            'localeCode' => $localeCode,
            'userId' => $userId
        ], 0));

        $this->cache[$cacheKey] = $result;

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountUsers implements CountQuery
{
    private const VOLUME_NAME = 'count_users';

    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): CountVolume
    {
        $sql = <<<SQL
SELECT COUNT(*) as count
FROM oro_user
WHERE oro_user.user_type = :type
SQL;

        $result = $this->connection->executeQuery($sql, ['type' => User::TYPE_USER])->fetchAssociative();
        $volume = new CountVolume((int) $result['count'], self::VOLUME_NAME);

        return $volume;
    }
}

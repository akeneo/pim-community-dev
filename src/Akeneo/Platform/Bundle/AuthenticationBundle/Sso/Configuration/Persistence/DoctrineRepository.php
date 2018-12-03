<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Persistence;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Root;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * Doctrine implementation for configuration repository.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class DoctrineRepository implements Repository
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $configTableName;

    public function __construct(
        Connection $connection,
        string $configTableName
    ) {
        $this->connection = $connection;
        $this->configTableName = $configTableName;
    }

    public function save(Root $config): void
    {
        $this->connection->executeQuery(
            'INSERT INTO akeneo_pim_configuration (code, values) VALUES(:code, :values) ON DUPLICATE KEY UPDATE values = VALUES(:values)',
            [
                'code'   => $config->code(),
                'values' => json_encode($config->toArray()),
            ]
        );
    }

    public function find(string $code): ?Root
    {
        $statement = $this->connection->prepare('SELECT * FROM akeneo_pim_configuration WHERE code = :code');
        $statement->bindValue('code', $code);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (null === $result) {
            return null;
        }

        return Root::fromArray(
            $result['code'],
            json_decode($result['values'], true)
        );
    }
}

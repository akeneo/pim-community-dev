<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Persistence\Sql;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * SQL implementation for configuration repository.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class SqlRepository implements Repository
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(Configuration $config): void
    {
        $statement = $this->connection->prepare('INSERT INTO pim_configuration (`code`, `values`) VALUES(:code, :values) ON DUPLICATE KEY UPDATE `values` = :values;');
        $statement->bindValue('code', (string) $config->code(), Types::STRING);
        $statement->bindValue('values', $config->toArray(), Types::JSON);

        $statement->executeStatement();
    }

    public function find(string $code): Configuration
    {
        $statement = $this->connection->prepare('SELECT * FROM pim_configuration WHERE code = :code;');
        $result = $statement->executeQuery(['code' => $code])->fetchAssociative();

        if (false === $result) {
            throw new ConfigurationNotFound(
                $code,
                sprintf('No configuration found for code "%s".', $code)
            );
        }

        return Configuration::fromArray(
            $result['code'],
            json_decode($result['values'], true)
        );
    }
}

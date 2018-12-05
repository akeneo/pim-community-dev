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
use Doctrine\DBAL\Types\Type;
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

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(Root $config): void
    {
        $statement = $this->connection->prepare('INSERT INTO pim_configuration (`code`, `values`) VALUES(:code, :values) ON DUPLICATE KEY UPDATE `values` = :values;');
        $statement->bindValue('code', $config->code(), Type::STRING);
        $statement->bindValue('values', $config->toArray(), Type::JSON_ARRAY);

        $statement->execute();
    }

    public function find(string $code): ?Root
    {
        $statement = $this->connection->prepare('SELECT * FROM pim_configuration WHERE code = :code;');
        $statement->execute(['code' => $code]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (false === $result) {
            return null;
        }

        return Root::fromArray(
            $result['code'],
            json_decode($result['values'], true)
        );
    }
}

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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * Doctrine implementation of the configuration repository.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ConfigurationRepository implements ConfigurationRepositoryInterface
{
    private const TOKEN = 'franklin_token';

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): Configuration
    {
        $configuration = new Configuration();
        $result = $this->connection->fetchAssoc(sprintf("SELECT `values` FROM pim_configuration WHERE code = '%s'", self::TOKEN));

        if (false !== $result) {
            $token = current(json_decode($result['values'], true));
            $configuration->setToken(new Token($token));
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Configuration $configuration): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO pim_configuration (`code`, `values`) VALUES(:code, :values) ON DUPLICATE KEY UPDATE `values` = :values;'
        );
        $statement->bindValue('code', self::TOKEN, Type::STRING);
        $statement->bindValue('values', [(string) $configuration->getToken()], Type::JSON_ARRAY);

        $statement->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->connection->executeQuery(sprintf('DELETE FROM pim_configuration WHERE code = "%s"', self::TOKEN));
    }
}

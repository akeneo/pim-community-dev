<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContext
{
    /** @var Connection */
    private $connection;

    /** @var bool */
    private $collectable;

    /** @var bool */
    private $areCredentialsValidCombination;

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    public function isCollectable(): bool
    {
        return $this->collectable;
    }

    public function setCollectable(bool $collectable): void
    {
        $this->collectable = $collectable;
    }

    public function areCredentialsValidCombination(): bool
    {
        return $this->areCredentialsValidCombination;
    }

    public function setAreCredentialsValidCombination(bool $areCredentialsValidCombination): void
    {
        $this->areCredentialsValidCombination = $areCredentialsValidCombination;
    }
}

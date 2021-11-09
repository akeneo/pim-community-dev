<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Handler;

use Akeneo\Connectivity\Connection\Application\CommandHandlerInterface;
use Akeneo\Connectivity\Connection\Application\TransactionnalCommandHandlerInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransactionnalHandler implements CommandHandlerInterface
{
    private TransactionnalCommandHandlerInterface $decorated;
    private Connection $connection;

    public function __construct(
        TransactionnalCommandHandlerInterface $decorated,
        Connection $connection
    ) {
        $this->decorated = $decorated;
        $this->connection = $connection;
    }

    public function __invoke($command)
    {
        $this->connection->beginTransaction();

        try {
            ($this->decorated)($command);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }
}

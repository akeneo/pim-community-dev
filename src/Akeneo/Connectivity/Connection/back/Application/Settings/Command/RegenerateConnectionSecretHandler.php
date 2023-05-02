<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecretInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionSecretHandler
{
    public function __construct(private ConnectionRepositoryInterface $repository, private RegenerateClientSecretInterface $regenerateClientSecret)
    {
    }

    public function handle(RegenerateConnectionSecretCommand $command): void
    {
        $connection = $this->repository->findOneByCode($command->code());
        if (null === $connection) {
            throw new \InvalidArgumentException(
                \sprintf('Connection with code "%s" does not exist', $command->code())
            );
        }

        $this->regenerateClientSecret->execute($connection->clientId());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPasswordInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionPasswordHandler
{
    private ConnectionRepositoryInterface $repository;

    private RegenerateUserPasswordInterface $regenerateUserPassword;

    public function __construct(ConnectionRepositoryInterface $repository, RegenerateUserPasswordInterface $regenerateUserPassword)
    {
        $this->repository = $repository;
        $this->regenerateUserPassword = $regenerateUserPassword;
    }

    public function handle(RegenerateConnectionPasswordCommand $command): string
    {
        $connection = $this->repository->findOneByCode($command->code());
        if (null === $connection) {
            throw new \InvalidArgumentException(
                \sprintf('Connection with code "%s" does not exist', $command->code())
            );
        }

        return $this->regenerateUserPassword->execute($connection->userId());
    }
}

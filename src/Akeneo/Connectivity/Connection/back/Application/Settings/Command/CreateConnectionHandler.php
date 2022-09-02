<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateConnectionHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private ConnectionRepositoryInterface $repository,
        private CreateClientInterface $createClient,
        private CreateUserInterface $createUser,
        private FindAConnectionHandler $findAConnectionHandler,
    ) {
    }

    public function handle(CreateConnectionCommand $command): ConnectionWithCredentials
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ConstraintViolationListException($violations);
        }

        $groups = null === $command->userGroup() ? null : [$command->userGroup()];

        $user = $this->createUser->execute($command->code(), $command->label(), ' ', $groups);
        $client = $this->createClient->execute($command->label());

        $connection = new Connection(
            $command->code(),
            $command->label(),
            $command->flowType(),
            $client->id(),
            $user->id(),
            null,
            $command->auditable(),
            $command->type()
        );
        $this->repository->create($connection);

        $query = new FindAConnectionQuery((string) $connection->code());
        $connectionDTO = $this->findAConnectionHandler->handle($query);
        if (null === $connectionDTO) {
            throw new \RuntimeException();
        }

        $connectionDTO->setPassword($user->password());

        return $connectionDTO;
    }
}

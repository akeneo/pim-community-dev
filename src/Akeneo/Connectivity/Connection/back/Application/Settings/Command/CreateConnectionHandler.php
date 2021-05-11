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
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateConnectionHandler
{
    private ConnectionRepository $repository;

    private CreateClientInterface $createClient;

    private ValidatorInterface $validator;

    private CreateUserInterface $createUser;

    private FindAConnectionHandler $findAConnectionHandler;

    public function __construct(
        ValidatorInterface $validator,
        ConnectionRepository $repository,
        CreateClientInterface $createClient,
        CreateUserInterface $createUser,
        FindAConnectionHandler $findAConnectionHandler
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->createClient = $createClient;
        $this->createUser = $createUser;
        $this->findAConnectionHandler = $findAConnectionHandler;
    }

    public function handle(CreateConnectionCommand $command): ConnectionWithCredentials
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ConstraintViolationListException($violations);
        }

        $user = $this->createUser->execute($command->code(), $command->label(), ' ');
        $client = $this->createClient->execute($command->label());

        $connection = new Connection(
            $command->code(),
            $command->label(),
            $command->flowType(),
            $client->id(),
            $user->id(),
            null,
            $command->auditable()
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

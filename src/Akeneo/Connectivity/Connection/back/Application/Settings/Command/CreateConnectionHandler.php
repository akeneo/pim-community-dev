<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
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
    /** @var ConnectionRepository */
    private $repository;

    /** @var CreateClientInterface */
    private $createClient;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateUserInterface */
    private $createUser;

    /** @var FindAConnectionHandler */
    private $findAConnectionHandler;

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
            new UserId($user->id())
        );
        $this->repository->create($connection);

        $connectionDTO = $this->findAConnectionHandler->handle(
            new FindAConnectionQuery((string) $connection->code())
        );
        $connectionDTO->setPassword($user->password());

        return $connectionDTO;
    }
}

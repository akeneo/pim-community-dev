<?php
declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppHandler
{
    /** @var AppRepository */
    private $repository;

    /** @var CreateClientInterface */
    private $createClient;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateUserInterface */
    private $createUser;

    public function __construct(
        ValidatorInterface $validator,
        AppRepository $repository,
        CreateClientInterface $createClient,
        CreateUserInterface $createUser
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->createClient = $createClient;
        $this->createUser = $createUser;
    }

    public function handle(CreateAppCommand $command): AppWithCredentials
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ConstraintViolationListException($violations);
        }

        $client = $this->createClient->execute($command->label());

        $randomNumberString = str_pad((string) rand(1, 9999), 4, "0");
        $username = sprintf('%s_%d', $command->code(), $randomNumberString);
        $user = $this->createUser->execute(
            $username,
            $command->label(),
            ' '
        );

        $app = new App(
            $command->code(),
            $command->label(),
            $command->flowType(),
            $client->id(),
            new UserId($user->id())
        );
        $this->repository->create($app);

        return new AppWithCredentials(
            $command->code(),
            $command->label(),
            $command->flowType(),
            $client->clientId(),
            $client->secret(),
            $user->username(),
            $user->password()
        );
    }
}

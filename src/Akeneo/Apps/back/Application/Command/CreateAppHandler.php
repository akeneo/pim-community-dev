<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
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

    public function __construct(
        ValidatorInterface $validator,
        AppRepository $repository,
        CreateClientInterface $createClient
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->createClient = $createClient;
    }

    public function handle(CreateAppCommand $command): void
    {
        // TODO: Validate code unicity
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ConstraintViolationListException($violations);
        }

        $clientId = $this->createClient->execute($command->label());

        $appId = $this->repository->generateId();
        $app = App::create(
            $appId,
            $command->code(),
            $command->label(),
            $command->flowType(),
            $clientId
        );
        $this->repository->create($app);
    }
}

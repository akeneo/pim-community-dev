<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;

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

    public function __construct(AppRepository $repository, CreateClientInterface $createClient)
    {
        $this->repository = $repository;
        $this->createClient = $createClient;
    }

    public function handle(CreateAppCommand $command): void
    {
        $clientId = $this->createClient->execute((string) $command->appLabel());

        // TODO: Validate code unicity
        $app = App::create(
            $command->appCode(),
            $command->appLabel(),
            $command->flowType(),
            $clientId
        );
        $this->repository->create($app);
    }
}

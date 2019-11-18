<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Service\RegenerateClientSecret;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateAppSecretHandler
{
    /** @var AppRepository */
    private $repository;
    /** @var RegenerateClientSecret */
    private $regenerateClientSecret;

    public function __construct(AppRepository $repository, RegenerateClientSecret $regenerateClientSecret)
    {
        $this->repository = $repository;
        $this->regenerateClientSecret = $regenerateClientSecret;
    }

    public function handle(RegenerateAppSecretCommand $command): void
    {
        $app = $this->repository->findOneByCode($command->code());
        if (null === $app) {
            throw new \InvalidArgumentException(
                sprintf('App with code "%s" does not exist', $command->code())
            );
        }

        $this->regenerateClientSecret->execute($app->clientId());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecret;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionSecretHandler
{
    /** @var ConnectionRepository */
    private $repository;

    /** @var RegenerateClientSecret */
    private $regenerateClientSecret;

    public function __construct(ConnectionRepository $repository, RegenerateClientSecret $regenerateClientSecret)
    {
        $this->repository = $repository;
        $this->regenerateClientSecret = $regenerateClientSecret;
    }

    public function handle(RegenerateConnectionSecretCommand $command): void
    {
        $connection = $this->repository->findOneByCode($command->code());
        if (null === $connection) {
            throw new \InvalidArgumentException(
                sprintf('Connection with code "%s" does not exist', $command->code())
            );
        }

        $this->regenerateClientSecret->execute($connection->clientId());
    }
}

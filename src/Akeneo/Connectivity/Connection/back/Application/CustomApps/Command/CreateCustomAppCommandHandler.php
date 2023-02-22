<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\CreateCustomAppQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCustomAppCommandHandler
{
    public function __construct(
        private readonly RandomCodeGeneratorInterface $randomCodeGenerator,
        private readonly CreateCustomAppQueryInterface $createCustomAppQuery,
    ) {
    }

    public function handle(CreateCustomAppCommand $command): void
    {
        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);

        $this->createCustomAppQuery->execute(
            $command->clientId,
            $command->name,
            $command->activateUrl,
            $command->callbackUrl,
            $clientSecret,
            $command->userId,
        );
    }
}

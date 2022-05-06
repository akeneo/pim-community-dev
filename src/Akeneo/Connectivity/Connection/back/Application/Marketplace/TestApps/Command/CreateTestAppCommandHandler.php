<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\CreateTestAppQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateTestAppCommandHandler
{
    public function __construct(
        private RandomCodeGeneratorInterface $randomCodeGenerator,
        private CreateTestAppQueryInterface $createTestAppQuery,
    ) {
    }

    public function handle(CreateTestAppCommand $command): void
    {
        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);

        $this->createTestAppQuery->execute(
            $command->getClientId(),
            $command->getName(),
            $command->getActivateUrl(),
            $command->getCallbackUrl(),
            $clientSecret,
            $command->getUserId(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\UpdateCustomAppSecretQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegenerateCustomAppSecretHandler
{
    public function __construct(
        private readonly UpdateCustomAppSecretQueryInterface $regenerateCustomAppSecretQuery,
        private readonly RandomCodeGeneratorInterface $randomCodeGenerator,
    ) {
    }

    public function handle(RegenerateCustomAppSecretCommand $command): void
    {
        $customAppId = $command->customAppId;

        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);

        $this->regenerateCustomAppSecretQuery->execute($customAppId, $clientSecret);
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SaveWebhookSecretQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateWebhookSecretHandler
{
    public function __construct(
        private GenerateSecretInterface $generateSecret,
        private SaveWebhookSecretQueryInterface $saveQuery
    ) {
    }

    public function handle(GenerateWebhookSecretCommand $command): string
    {
        $newSecret = $this->generateSecret->generate();
        $secretSaved = $this->saveQuery->execute($command->connectionCode(), $newSecret);
        if (!$secretSaved) {
            throw new ConnectionWebhookNotFoundException();
        }

        return $newSecret;
    }
}

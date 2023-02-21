<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure;

use Akeneo\Pim\Platform\Messaging\Domain\MessageTenantAwareInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessageHandler implements MessageHandlerInterface
{
    public function __construct(private readonly object $handlerName)
    {

    }

    public function __invoke(MessageTenantAwareInterface $message)
    {
        // TODO: Launch a new process
        $tenantId = $message->getTenantId();

        print_r("tenantId = $tenantId\n");

        ($this->handlerName)($message);
    }
}

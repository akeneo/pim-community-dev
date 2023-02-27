<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Handler;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Message\LaunchProductAndProductModelEvaluationsMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandler implements MessageHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        // @TODO: JEL-228
        $this->logger->debug('Handler ' . get_class($this) . ' received a message: ' . $message->text, [
            'correlation_id' => $message->getCorrelationId(),
            'tenant_id' => $message->getTenantId(),
        ]);
    }
}

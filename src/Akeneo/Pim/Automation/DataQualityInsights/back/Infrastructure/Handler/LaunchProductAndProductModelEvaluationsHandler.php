<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Handler;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Message\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Tool\Component\Messenger\TraceableMessageHandlerInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandler implements TraceableMessageHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(TraceableMessageInterface $message): void
    {
        Assert::isInstanceOf($message, LaunchProductAndProductModelEvaluationsMessage::class);

        // @TODO: JEL-228
        $this->logger->debug('Handler ' . get_class($this) . ' received a message: ' . $message->text, [
            'correlation_id' => $message->getCorrelationId(),
            'tenant_id' => $message->getTenantId(),
        ]);
    }
}

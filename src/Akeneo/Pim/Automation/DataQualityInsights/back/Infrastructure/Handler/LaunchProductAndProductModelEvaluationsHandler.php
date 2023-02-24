<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Handler;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Message\LaunchProductAndProductModelEvaluationsMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandler implements MessageHandlerInterface
{
    public function __invoke(LaunchProductAndProductModelEvaluationsMessage $message)
    {
        print_r(get_class($this) . ': ' . $message->text . "\n");
        print_r('correlation_id = ' . $message->getCorrelationId() . "\n");
        print_r('tenant_id = ' . $message->getTenantId() . "\n");
    }
}

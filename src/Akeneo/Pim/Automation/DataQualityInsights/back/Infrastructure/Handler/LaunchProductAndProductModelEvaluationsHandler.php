<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Handler;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Message\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Pim\Platform\Messaging\Domain\PimMessageHandlerInterface;
use Akeneo\Pim\Platform\Messaging\Domain\PimMessageInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandler implements PimMessageHandlerInterface
{
    public function __invoke(PimMessageInterface $message)
    {
        Assert::isInstanceOf($message, LaunchProductAndProductModelEvaluationsMessage::class);
        print_r(get_class($this) . ': ' . $message->text . "\n");
    }
}

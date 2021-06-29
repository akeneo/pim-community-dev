<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookReachabilityHandlerSpec extends ObjectBehavior
{
    public function let(WebhookReachabilityChecker $reachabilityChecker): void
    {
        $this->beConstructedWith($reachabilityChecker);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CheckWebhookReachabilityHandler::class);
    }

    public function it_returns_url_reachability_status($reachabilityChecker): void
    {
        $command = new CheckWebhookReachabilityCommand('http://172.17.0.1:8000/webhook', '1234');
        $expectedUrlReachabilityStatus = new UrlReachabilityStatus(true, "200: OK");

        $reachabilityChecker->check($command->webhookUrl(), $command->secret())->willReturn($expectedUrlReachabilityStatus);

        $handleResult = $this->handle($command);

        Assert::assertEquals($expectedUrlReachabilityStatus, $handleResult->getWrappedObject());
    }
}


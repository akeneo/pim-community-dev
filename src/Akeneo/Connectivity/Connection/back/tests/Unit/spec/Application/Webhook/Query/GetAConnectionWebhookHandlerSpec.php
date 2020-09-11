<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery as CqrsQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQuery as DbQuery;
use PhpSpec\ObjectBehavior;

class GetAConnectionWebhookHandlerSpec extends ObjectBehavior
{
    public function let(DbQuery $dbQuery): void
    {
        $this->beConstructedWith($dbQuery);
    }

    public function it_is_a_handler(): void
    {
        $this->shouldHaveType(GetAConnectionWebhookHandler::class);
    }

    public function it_gets_a_connection_webhook_given_a_provided_code($dbQuery): void
    {
        $cqrsQuery = new CqrsQuery('magento');
        $connectionWebhook = new ConnectionWebhook(
            'magento',
            true,
            '1234_secret',
            'any-url.com'
        );
        $dbQuery->execute('magento')->willReturn($connectionWebhook);

        $this->handle($cqrsQuery)->shouldReturn($connectionWebhook);
    }

    public function it_returns_null_if_no_connection_webhook_exists($dbQuery): void
    {
        $cqrsQuery = new CqrsQuery('magento');
        $dbQuery->execute('magento')->willReturn(null);

        $this->handle($cqrsQuery)->shouldReturn(null);
    }
}

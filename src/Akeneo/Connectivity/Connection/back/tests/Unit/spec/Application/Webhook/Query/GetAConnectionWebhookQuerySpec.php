<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery;
use PhpSpec\ObjectBehavior;

class GetAConnectionWebhookQuerySpec extends ObjectBehavior
{
    public function it_is_a_get_a_connection_webhook_query(): void
    {
        $this->beConstructedWith('magento');
        $this->shouldHaveType(GetAConnectionWebhookQuery::class);
    }

    public function it_provides_a_code(): void
    {
        $this->beConstructedWith('magento');

        $this->code()->shouldReturn('magento');
    }
}

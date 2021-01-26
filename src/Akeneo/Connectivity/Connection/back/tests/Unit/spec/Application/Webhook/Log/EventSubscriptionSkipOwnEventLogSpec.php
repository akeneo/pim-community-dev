<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionSkipOwnEventLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Platform\Component\EventQueue\Author;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionSkipOwnEventLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $webhook = new ActiveWebhook(
            'ecommerce',
            1,
            'secret1234',
            'https://test.com'
        );

        $event =
            new ProductCreated(
                Author::fromNameAndType('julia', Author::TYPE_UI),
                ['identifier' => '1'],
                1603935337,
                'fe904867-9428-4d97-bfa9-7aa13c0ee0bf'
            );


        $this->beConstructedThrough('fromEvent', [$event, 'ecommerce']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventSubscriptionSkipOwnEventLog::class);
    }

    public function it_returns_the_log(): void
    {
        $this->toLog()->shouldReturn(
            [
                'type' => EventSubscriptionSkipOwnEventLog::TYPE,
                'connection_code' => 'ecommerce',
                'event' => [
                    'uuid' => 'fe904867-9428-4d97-bfa9-7aa13c0ee0bf',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'name' => 'product.created',
                    'timestamp' => 1603935337,
                ],
            ]
        );
    }

}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsTransportFactory;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsTransportFactorySpec extends ObjectBehavior
{
    public function let(PubSubClientFactory $pubSubClientFactory, OrderingKeySolver $orderingKeySolver): void
    {
        $this->beConstructedWith($pubSubClientFactory, $orderingKeySolver);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GpsTransportFactory::class);
    }

    public function it_supports_the_gps_dsn(): void
    {
        $this->supports('gps:', [])->shouldBe(true);
    }
}

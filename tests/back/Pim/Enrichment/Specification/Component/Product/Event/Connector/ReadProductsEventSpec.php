<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Event\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReadProductsEventSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(3, ReadProductsEvent::REST_API_TYPE, 'code');
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(ReadProductsEvent::class);
    }

    public function it_provides_the_number_of_read_products()
    {
        $this->beConstructedWith(5);
        $this->getCount()->shouldReturn(5);
    }

    public function it_returns_a_connection_code()
    {
        $this->beConstructedWith(5, ReadProductsEvent::REST_API_TYPE, 'code');
        $this->getConnectionCode()->shouldReturn('code');
    }

    public function it_comes_from_the_events_api()
    {
        $this->beConstructedWith(5, ReadProductsEvent::EVENTS_API_TYPE, 'code');
        $this->isEventsApi()->shouldReturn(true);
    }

    public function it_returns_false_if_no_events_api_event()
    {
        $this->beConstructedWith(5, ReadProductsEvent::REST_API_TYPE, 'code');
        $this->isEventsApi()->shouldReturn(false);
    }

    public function it_is_not_an_events_api_event_by_default()
    {
        $this->beConstructedWith(5);
        $this->isEventsApi()->shouldReturn(false);
    }
}

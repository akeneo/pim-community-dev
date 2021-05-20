<?php

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
        $this->beConstructedWith(3, 'code');
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(ReadProductsEvent::class);
    }

    public function it_returns_a_counter()
    {
        $this->beConstructedWith(5);
        $this->getCount()->shouldReturn(5);
    }

    public function it_returns_a_connection_code()
    {
        $this->beConstructedWith(5, 'code');
        $this->getConnectionCode()->shouldReturn('code');
    }

    public function it_is_a_event_api_if_connection_code_is_defined()
    {
        $this->beConstructedWith(5, 'code');
        $this->isEventApi()->shouldReturn(true);
    }

    public function it_is_not_a_event_api_if_connection_code_is_not_defined()
    {
        $this->beConstructedWith(5);
        $this->isEventApi()->shouldReturn(false);
    }
}
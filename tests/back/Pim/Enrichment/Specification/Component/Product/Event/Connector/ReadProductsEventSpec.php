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
        $this->beConstructedWith(3, 'code');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReadProductsEvent::class);
    }

    public function it_provides_the_number_of_read_products()
    {
        $this->getCount()->shouldReturn(3);
    }

    public function it_returns_a_connection_code()
    {
        $this->getConnectionCode()->shouldReturn('code');
    }
}

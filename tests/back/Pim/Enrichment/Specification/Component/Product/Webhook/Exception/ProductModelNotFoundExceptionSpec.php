<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNotFoundExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith("identifier");
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNotFoundException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldBeAnInstanceOf(\RuntimeException::class);
    }

    function it_is_an_event_building_exception()
    {
        $this->shouldImplement(EventBuildingExceptionInterface::class);
    }

    function it_returns_a_message()
    {
        $this->getMessage()->shouldReturn('Product Model "identifier" not found');
    }
}

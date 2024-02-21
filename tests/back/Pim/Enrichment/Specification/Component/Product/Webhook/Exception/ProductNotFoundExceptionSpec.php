<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNotFoundExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(Uuid::fromString('f496b00a-7d44-4fb0-9e0b-a77b7fdea342'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNotFoundException::class);
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
        $this->getMessage()->shouldReturn('Product "f496b00a-7d44-4fb0-9e0b-a77b7fdea342" not found');
    }
}

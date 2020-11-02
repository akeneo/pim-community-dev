<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotGrantedCategoryExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $exception = new \Exception('previous exception');
        $this->beConstructedWith('exception message', $exception);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedCategoryException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    function it_is_an_event_building_exception()
    {
        $this->shouldImplement(EventBuildingExceptionInterface::class);
    }

    function it_returns_a_message()
    {
        $this->getMessage()->shouldReturn('exception message');
    }

    function it_returns_a_previous_exception()
    {
        $this->getPrevious()->getMessage()->shouldReturn('previous exception');
    }
}

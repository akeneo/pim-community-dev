<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\Write;

use Akeneo\Apps\Domain\Model\Write\AppLabel;
use Akeneo\Apps\Domain\Model\Write\FlowType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FlowTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedThrough('create', [FlowType::OTHER]);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    function it_cannot_be_created_with_an_unknown_flow_type()
    {
        $this->beConstructedThrough('create', ['foo']);
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_apps.app.constraint.flow_type.invalid')
        )->duringInstantiation();
    }

    function it_creates_a_data_destination_flow_type()
    {
        $this->beConstructedThrough('create', [FlowType::DATA_DESTINATION]);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    function it_creates_a_data_source_flow_type()
    {
        $this->beConstructedThrough('create', [FlowType::DATA_SOURCE]);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    function it_creates_an_others_flow_type()
    {
        $this->beConstructedThrough('create', [FlowType::OTHER]);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    function it_returns_the_flow_type_as_string()
    {
        $this->beConstructedThrough('create', [FlowType::DATA_SOURCE]);
        $this->__toString()->shouldReturn(FlowType::DATA_SOURCE);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FlowTypeSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(FlowType::OTHER);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    public function it_cannot_be_created_with_an_unknown_flow_type()
    {
        $this->beConstructedWith('foo');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.flow_type.invalid')
        )->duringInstantiation();
    }

    public function it_creates_a_data_destination_flow_type()
    {
        $this->beConstructedWith(FlowType::DATA_DESTINATION);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    public function it_creates_a_data_source_flow_type()
    {
        $this->beConstructedWith(FlowType::DATA_SOURCE);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    public function it_creates_an_others_flow_type()
    {
        $this->beConstructedWith(FlowType::OTHER);
        $this->shouldBeAnInstanceOf(FlowType::class);
    }

    public function it_returns_the_flow_type_as_string()
    {
        $this->beConstructedWith(FlowType::DATA_SOURCE);
        $this->__toString()->shouldReturn(FlowType::DATA_SOURCE);
    }
}

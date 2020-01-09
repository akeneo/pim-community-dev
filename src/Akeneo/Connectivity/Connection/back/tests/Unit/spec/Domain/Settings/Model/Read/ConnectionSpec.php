<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'a/b/c/the_path.jpg'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Connection::class);
    }

    public function it_returns_the_code()
    {
        $this->code()->shouldReturn('magento');
    }

    public function it_returns_the_label()
    {
        $this->label()->shouldReturn('Magento Connector');
    }

    public function it_returns_null_if_there_is_no_image()
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION
        );
        $this->image()->shouldReturn(null);
    }

    public function it_returns_the_image()
    {
        $this->image()->shouldReturn('a/b/c/the_path.jpg');
    }

    public function it_returns_the_flow_type()
    {
        $this->flowType()->shouldReturn(FlowType::DATA_DESTINATION);
    }

    public function it_normalizes_a_connection()
    {
        $this->normalize()->shouldReturn([
            'code' => 'magento',
            'label' => 'Magento Connector',
            'flowType' => FlowType::DATA_DESTINATION,
            'image' => 'a/b/c/the_path.jpg'
        ]);
    }
}

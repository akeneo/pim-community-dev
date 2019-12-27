<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionCommandSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('Magento', 'Magento Connector', FlowType::DATA_DESTINATION);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateConnectionCommand::class);
    }

    public function it_returns_the_code()
    {
        $this->code()->shouldReturn('Magento');
    }

    public function it_returns_the_label()
    {
        $this->label()->shouldReturn('Magento Connector');
    }

    public function it_returns_the_flow_type()
    {
        $this->flowType()->shouldReturn(FlowType::DATA_DESTINATION);
    }
}

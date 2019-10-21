<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\Read;

use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(App::class);
    }

    function it_returns_the_code()
    {
        $this->code()->shouldReturn('magento');
    }

    function it_returns_the_label()
    {
        $this->label()->shouldReturn('Magento Connector');
    }

    function it_returns_the_flow_type()
    {
        $this->flowType()->shouldReturn(FlowType::DATA_DESTINATION);
    }

    function it_normalizes_an_app()
    {
        $this->normalize()->shouldReturn([
            'code' => 'magento',
            'label' => 'Magento Connector',
            'flowType' => FlowType::DATA_DESTINATION
        ]);
    }
}

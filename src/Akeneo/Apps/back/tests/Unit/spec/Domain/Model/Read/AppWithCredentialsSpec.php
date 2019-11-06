<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\Read;

use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppWithCredentialsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'my_custom_client_id',
            'my_secret'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AppWithCredentials::class);
    }

    public function it_returns_the_code()
    {
        $this->code()->shouldReturn('magento');
    }

    public function it_returns_the_label()
    {
        $this->label()->shouldReturn('Magento Connector');
    }

    public function it_returns_the_flow_type()
    {
        $this->flowType()->shouldReturn(FlowType::DATA_DESTINATION);
    }

    public function it_returns_the_client_id()
    {
        $this->clientId()->shouldReturn('my_custom_client_id');
    }

    public function it_returns_the_secret()
    {
        $this->secret()->shouldReturn('my_secret');
    }

    public function it_normalizes_an_app_with_credentials()
    {
        $this->normalize()->shouldReturn([
            'code' => 'magento',
            'label' => 'Magento Connector',
            'flow_type' => FlowType::DATA_DESTINATION,
            'client_id' => 'my_custom_client_id',
            'secret' => 'my_secret',
        ]);
    }
}

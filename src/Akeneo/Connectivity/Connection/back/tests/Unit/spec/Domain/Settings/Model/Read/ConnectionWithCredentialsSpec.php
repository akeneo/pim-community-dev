<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionWithCredentialsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'a/b/c/the_path.jpg',
            'my_custom_client_id',
            'my_secret',
            'my_username',
            '1',
            '2'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ConnectionWithCredentials::class);
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

    public function it_returns_the_username()
    {
        $this->username()->shouldReturn('my_username');
    }

    public function it_returns_null_when_the_password_is_not_set()
    {
        $this->password()->shouldReturn(null);
    }

    public function it_sets_the_password()
    {
        $this->password()->shouldReturn(null);

        $this->setPassword('my_password');

        $this->password()->shouldReturn('my_password');
    }

    public function it_returns_null_if_there_is_no_image()
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            'my_custom_client_id',
            'my_secret',
            'my_username',
            '1',
            '2'
        );
        $this->image()->shouldBeNull();
    }

    public function it_returns_the_image()
    {
        $this->image()->shouldReturn('a/b/c/the_path.jpg');
    }

    public function it_returns_the_user_role_id()
    {
        $this->userRoleId()->shouldReturn('1');
    }

    public function it_returns_the_user_group_id()
    {
        $this->userGroupId()->shouldReturn('2');
    }

    public function it_normalizes_a_connection_with_credentials()
    {
        $this->setPassword('my_password');

        $this->normalize()->shouldReturn([
            'code' => 'magento',
            'label' => 'Magento Connector',
            'flow_type' => FlowType::DATA_DESTINATION,
            'image' => 'a/b/c/the_path.jpg',
            'client_id' => 'my_custom_client_id',
            'secret' => 'my_secret',
            'username' => 'my_username',
            'password' => 'my_password',
            'user_role_id' => '1',
            'user_group_id' => '2'
        ]);
    }
}

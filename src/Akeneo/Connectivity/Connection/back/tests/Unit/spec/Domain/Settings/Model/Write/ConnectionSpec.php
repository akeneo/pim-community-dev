<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
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
            42,
            new UserId(24)
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Connection::class);
    }

    public function it_returns_the_code()
    {
        $this->code()->shouldBeLike(new ConnectionCode('magento'));
    }

    public function it_returns_the_label()
    {
        $this->label()->shouldBeLike(new ConnectionLabel('Magento Connector'));
    }

    public function it_returns_the_flow_type()
    {
        $this->flowType()->shouldBeLike(new FlowType(FlowType::DATA_DESTINATION));
    }

    public function it_returns_the_client_id()
    {
        $this->clientId()->shouldBeLike(new ClientId(42));
    }

    public function it_returns_the_user_id()
    {
        $this->userId()->shouldBeLike(new UserId(24));
    }

    public function it_provides_the_image()
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            42,
            new UserId(24),
            'a/b/c/image_path.jpg'
        );

        $this->image()->shouldBeLike(new ConnectionImage('a/b/c/image_path.jpg'));
    }

    public function it_is_instantiable_without_image()
    {
        $this->image()->shouldBeNull();
    }

    public function it_changes_the_image()
    {
        $this->image()->shouldBeNull();
        $image = new ConnectionImage('a/b/c/image_path.jpg');
        $this->setImage($image);
        $this->image()->shouldReturn($image);
    }

    public function it_changes_the_label()
    {
        $this->label()->shouldBeLike(new ConnectionLabel('Magento Connector'));

        $this->setLabel(new ConnectionLabel('Bynder'));
        $this->label()->shouldBeLike(new ConnectionLabel('Bynder'));
    }

    public function it_changes_the_flow_type()
    {
        $this->flowType()->shouldBeLike(new FlowType(FlowType::DATA_DESTINATION));

        $this->setFlowType(new FlowType(FlowType::OTHER));
        $this->flowType()->shouldBeLike(new FlowType(FlowType::OTHER));
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\Write;

use Akeneo\Apps\Domain\Model\ValueObject\AppId;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '42',
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            new ClientId(42),
            new UserId(24)
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(App::class);
    }

    public function it_returns_the_id()
    {
        $this->id()->shouldBeLike(new AppId('42'));
    }

    public function it_returns_the_code()
    {
        $this->code()->shouldBeLike(new AppCode('magento'));
    }

    public function it_returns_the_label()
    {
        $this->label()->shouldBeLike(new AppLabel('Magento Connector'));
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
}

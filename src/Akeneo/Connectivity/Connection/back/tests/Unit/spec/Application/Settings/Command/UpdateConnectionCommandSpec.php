<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            '1',
            '2',
            true
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UpdateConnectionCommand::class);
    }

    public function it_returns_the_code(): void
    {
        $this->code()->shouldReturn('magento');
    }

    public function it_returns_the_label(): void
    {
        $this->label()->shouldReturn('Magento Connector');
    }

    public function it_returns_the_flow_type(): void
    {
        $this->flowType()->shouldReturn(FlowType::DATA_DESTINATION);
    }

    public function it_returns_null_if_there_is_no_image(): void
    {
        $this->image()->shouldReturn(null);
    }

    public function it_returns_the_image(): void
    {
        $this->beConstructedWith(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'a/b/c/the_path.jpg',
            '1',
            '2',
            false
        );
        $this->image()->shouldReturn('a/b/c/the_path.jpg');
    }

    public function it_returns_the_user_role_id(): void
    {
        $this->userRoleId()->shouldReturn('1');
    }

    public function it_returns_the_user_group_id(): void
    {
        $this->userGroupId()->shouldReturn('2');
    }

    public function it_returns_the_auditable(): void
    {
        $this->auditable()->shouldReturn(true);
    }
}

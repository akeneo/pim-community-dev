<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppCommand;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('testAppId');
    }

    public function it_is_a_delete_test_app_command(): void
    {
        $this->shouldHaveType(DeleteTestAppCommand::class);
    }

    public function it_provides_the_test_app_id(): void
    {
        $this->getTestAppId()->shouldReturn('testAppId');
    }
}

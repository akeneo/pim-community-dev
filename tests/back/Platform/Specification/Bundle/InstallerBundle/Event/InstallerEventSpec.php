<?php

namespace Specification\Akeneo\Platform\Bundle\InstallerBundle\Event;

use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerEventSpec extends ObjectBehavior
{
    function let(CommandExecutor $commandExecutor)
    {
        $this->beConstructedWith($commandExecutor);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(InstallerEvent::class);
    }

    function it_is_a_generic_event()
    {
        $this->shouldHaveType(GenericEvent::class);
    }

    function it_provides_a_command_executor($commandExecutor)
    {
        $this->getCommandExecutor()->shouldReturn($commandExecutor);
    }
}

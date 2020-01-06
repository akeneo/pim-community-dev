<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionCommandSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('magento');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DeleteConnectionCommand::class);
    }

    public function it_returns_the_code()
    {
        $this->code()->shouldReturn('magento');
    }
}

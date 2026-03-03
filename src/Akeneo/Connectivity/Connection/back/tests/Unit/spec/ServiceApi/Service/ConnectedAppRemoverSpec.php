<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\ServiceApi\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppRemover;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectedAppRemoverSpec extends ObjectBehavior
{
    public function it_is_initializable(DeleteAppHandler $deleteAppHandler): void
    {
        $this->beConstructedWith($deleteAppHandler);
        $this->shouldBeAnInstanceOf(ConnectedAppRemover::class);
    }

    public function it_deletes_a_connected_app(DeleteAppHandler $deleteAppHandler): void
    {
        $this->beConstructedWith($deleteAppHandler);
        $deleteAppHandler->handle(Argument::type(DeleteAppCommand::class))->shouldBeCalled();
        $this->remove('fake_id');
    }
}

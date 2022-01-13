<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\DeleteTestAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppHandlerSpec extends ObjectBehavior
{
    public function let(
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteTestAppQueryInterface $deleteTestAppQuery,
    ): void
    {
        $this->beConstructedWith(
            $getTestAppQuery,
            $deleteTestAppQuery,
        );
    }

    public function it_is_delete_test_app_handler(): void
    {
        $this->shouldHaveType(DeleteTestAppHandler::class);
    }

    public function it_throws_when_attempting_to_delete_unknown_test_app(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [
            new DeleteTestAppCommand('unknown_test_app_id')
        ]);
    }

    public function it_deletes_test_app(
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteTestAppQueryInterface $deleteTestAppQuery,
    ): void {
        $testAppId = 'test_app_id';

        $getTestAppQuery->execute($testAppId)->willReturn([
            'id' => $testAppId,
            'name' => 'test_app_name',
            'author' => 'author name',
            'activate_url' => 'https://www.akeneo.com',
            'callback_url' => 'https://www.akeneo.com',
            'connected' => false,
        ]);

        $deleteTestAppQuery->execute($testAppId)->shouldBeCalled();

        $this->handle(new DeleteTestAppCommand($testAppId));
    }
}

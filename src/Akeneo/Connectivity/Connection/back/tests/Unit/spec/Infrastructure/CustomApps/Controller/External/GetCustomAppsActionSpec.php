<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\GetCustomAppsAction;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppsActionSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        GetCustomAppsQueryInterface $getCustomAppsQuery,
    ): void {
        $this->beConstructedWith(
            $tokenStorage,
            $getCustomAppsQuery,
        );
    }

    public function it_is_a_get_custom_apps_action(): void
    {
        $this->shouldHaveType(GetCustomAppsAction::class);
    }

    public function it_throws_a_bad_request_exception_when_token_storage_have_no_token(
        Request $request,
        TokenStorageInterface $tokenStorage,
    ): void {
        $tokenStorage->getToken()->willReturn(null);

        $this
            ->shouldThrow(new BadRequestHttpException('Invalid user token.'))
            ->during('__invoke', [$request]);
    }
}

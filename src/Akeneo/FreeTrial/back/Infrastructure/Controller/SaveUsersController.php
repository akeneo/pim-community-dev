<?php


namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Akeneo\FreeTrial\Application\InviteUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveUsersController
{
    private InviteUser $inviteUser;

    public function __construct(InviteUser $inviteUser)
    {
        $this->inviteUser = $inviteUser;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $emails = json_decode($request->getContent());

        foreach ($emails as $email) {
            ($this->inviteUser) ($email);
        }

        return new JsonResponse([]);
    }
}
<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RetrieveUsersController
{
    private GetInvitedUsersQuery $getInvitedUsersQuery;

    private FeatureFlag $freeTrialFeature;

    public function __construct(GetInvitedUsersQuery $getInvitedUsersQuery, FeatureFlag $freeTrialFeature)
    {
        $this->getInvitedUsersQuery = $getInvitedUsersQuery;
        $this->freeTrialFeature = $freeTrialFeature;
    }

    public function __invoke(): JsonResponse
    {
        if (!$this->freeTrialFeature->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $invitedUsers = $this->getInvitedUsersQuery->execute();

        $invitedUsers = array_map(fn (InvitedUser $invitedUser) => $invitedUser->toArray(), $invitedUsers);

        return new JsonResponse($invitedUsers);
    }
}
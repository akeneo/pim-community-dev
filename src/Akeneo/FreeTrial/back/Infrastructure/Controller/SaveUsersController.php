<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Akeneo\FreeTrial\Application\InviteUser;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveUsersController
{
    private InviteUser $inviteUser;

    private FeatureFlag $freeTrialFeature;

    public function __construct(InviteUser $inviteUser, FeatureFlag $freeTrialFeature)
    {
        $this->inviteUser = $inviteUser;
        $this->freeTrialFeature = $freeTrialFeature;
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->freeTrialFeature->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $emails = json_decode($request->getContent());

        foreach ($emails as $email) {
            ($this->inviteUser) ($email);
        }

        return new JsonResponse([]);
    }
}
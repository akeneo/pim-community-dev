<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MassDeleteAttributeController
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private JobLauncherInterface $jobLauncher,
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function launchAction(Request $request): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_attribute_mass_delete')) {
            return new JsonResponse(status: Response::HTTP_FORBIDDEN);
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('delete_attributes');
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $configuration = json_decode($request->getContent(), true);
        $configuration['users_to_notify'] = [$user->getUserIdentifier()];
        $configuration['send_email'] = true;

        $this->jobLauncher->launch($jobInstance, $user, $configuration);

        return new JsonResponse();
    }
}

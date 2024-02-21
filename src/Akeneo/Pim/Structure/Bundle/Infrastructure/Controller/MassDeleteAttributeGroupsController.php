<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MassDeleteAttributeGroupsController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JobLauncherInterface $jobLauncher,
        private readonly IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private readonly SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('delete_attribute_groups');
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(status: Response::HTTP_BAD_REQUEST);
        }

        if (!$user instanceof UserInterface) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->securityFacade->isGranted('pim_enrich_attributegroup_mass_delete')) {
            return new JsonResponse(status: Response::HTTP_FORBIDDEN);
        }

        $attributeGroupCodes = $request->get('codes');

        $replacementAttributeCode = $request->get('replacement_attribute_group', AttributeGroupInterface::DEFAULT_CODE);

        $configuration = [
            'filters' => [
                'codes' => $attributeGroupCodes,
            ],
            'replacement_attribute_group_code' => $replacementAttributeCode,
            'users_to_notify' => [$user->getUserIdentifier()],
            'send_email' => true,
        ];

        $this->jobLauncher->launch($jobInstance, $user, $configuration);

        return new JsonResponse(status: Response::HTTP_OK);
    }
}

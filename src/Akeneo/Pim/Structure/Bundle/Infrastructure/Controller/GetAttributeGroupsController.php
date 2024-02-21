<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Infrastructure\Controller;

use Akeneo\Pim\Structure\Bundle\Application\GetAttributesGroup\GetAttributeGroupsHandler;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class GetAttributeGroupsController
{
    public function __construct(
        private readonly SecurityFacadeInterface $securityFacade,
        private readonly GetAttributeGroupsHandler $getAttributeGroupsHandler
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_attributegroup_index')) {
            throw new AccessDeniedHttpException();
        }

        $attributeGroups = $this->getAttributeGroupsHandler->handle();

        return new JsonResponse($attributeGroups);
    }
}

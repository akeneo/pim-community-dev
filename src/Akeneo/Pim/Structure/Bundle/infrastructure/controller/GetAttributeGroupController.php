<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\infrastructure\controller;

use Akeneo\Pim\Structure\Bundle\Application\GetAttributeGroup\GetAttributeGroupHandler;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeGroupController
{
    public function __construct(
        private readonly SecurityFacadeInterface $securityFacade,
        private readonly GetAttributeGroupHandler $getAttributeGroupHandler
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->securityFacade->isGranted('pim_api_attribute_group_list')) {
            throw AccessDeniedException::create(__CLASS__, __METHOD__);
        }

        $attributeGroups = $this->getAttributeGroupHandler->handle();

        return new JsonResponse($attributeGroups);
    }
}

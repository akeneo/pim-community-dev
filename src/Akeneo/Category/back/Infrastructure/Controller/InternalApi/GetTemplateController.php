<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateController
{
    public function __construct(
        private SecurityFacade $securityFacade
    ) {
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_edit') === false) {
            // even if this is a read endpoint, the user must be granted edition rights
            // as this should only be used in the purpose of updating a category from the UI
            throw new AccessDeniedException();
        }

        // TODO : get template by identifier

        // TODO : normalize template

        return new JsonResponse("getTemplateController");
    }

}

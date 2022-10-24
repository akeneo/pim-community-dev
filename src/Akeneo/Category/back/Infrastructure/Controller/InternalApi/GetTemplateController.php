<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Query\GetTemplate;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateController
{
    public function __construct(
        private SecurityFacade $securityFacade,
        private GetTemplate $getTemplate
    ) {
    }

    public function __invoke(Request $request, string $templateUuid): JsonResponse
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_edit') === false) {
            // even if this is a read endpoint, the user must be granted edition rights
            // as this should only be used for the purpose of updating a category from the UI
            throw new AccessDeniedException();
        }

        $template = $this->getTemplate->byUuid($templateUuid);
        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($template->normalize(), Response::HTTP_OK);
    }

    public function getTemplateByCategoryTreeId(int $categoryId): JsonResponse
    {

        return new JsonResponse('toto');
    }
}

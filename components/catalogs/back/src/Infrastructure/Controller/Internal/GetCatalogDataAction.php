<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\FindCatalogProductSelectionCriteriaQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogDataAction
{
    public function __construct(
        private FindCatalogProductSelectionCriteriaQueryInterface $findCatalogProductSelectionCriteriaQuery,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productSelectionCriteria = $this->findCatalogProductSelectionCriteriaQuery->execute($catalogId);
        if (null === $productSelectionCriteria) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        return new JsonResponse([
            'product_selection_criteria' => $productSelectionCriteria,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogErrorsAction
{
    public function __construct(
        private ValidatorInterface $validator,
        private NormalizerInterface $normalizer,
        private GetCatalogQueryInterface $getCatalogQuery,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $catalogDomain = $this->getCatalogQuery->execute($catalogId);
        } catch (CatalogNotFoundException) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        $catalogNormalized = [
            'enabled' => $catalogDomain->isEnabled(),
            'product_selection_criteria' => $catalogDomain->getProductSelectionCriteria(),
            'product_value_filters' => $catalogDomain->getProductValueFilters(),
            'product_mapping' => $catalogDomain->getProductMapping(),
        ];

        $violations = $this->validator->validate($catalogNormalized, [
            new CatalogUpdatePayload(
                productMappingSchemaFile: \sprintf('%s_product.json', $catalog->getId())
            ),
        ]);

        $normalizedViolations = $violations->count() > 0 ? $this->normalizer->normalize($violations) : [];

        return new JsonResponse($normalizedViolations, Response::HTTP_OK);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
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
final class UpdateCatalogAction
{
    public function __construct(
        private ValidatorInterface $validator,
        private GetCatalogQueryInterface $getCatalogQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $catalog = $this->getCatalogQuery->execute($catalogId);
        } catch (CatalogNotFoundException) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        /**
         * @var array{
         *      enabled: bool,
         *      product_selection_criteria: array<int, array{field: string, operator: string, value?: mixed}>,
         *      product_value_filters: array{channels?: array<string>, locales?: array<string>},
         *      product_mapping: array<string, array{source: string, scope: string|null, locale: string|null}>
         * } $payload
         */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $catalog = new Catalog(
            $catalog->getId(),
            $catalog->getName(),
            $catalog->getOwnerUsername(),
            $payload['enabled'],
            $payload['product_selection_criteria'],
            $payload['product_value_filters'],
            $payload['product_mapping'],
        );

        $violations = $this->validator->validate($catalog);

        if ($violations->count() > 0) {
            return new JsonResponse(
                [
                    'errors' => $this->normalizer->normalize($violations),
                    'message' => 'Catalog is not valid.',
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->upsertCatalogQuery->execute($catalog);

        return new JsonResponse(null, 204);
    }
}

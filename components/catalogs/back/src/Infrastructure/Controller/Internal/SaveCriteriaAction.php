<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\Criteria;
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
final class SaveCriteriaAction
{
    public function __construct(
        private ValidatorInterface $validator,
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
        private UpdateCatalogProductSelectionCriteriaQueryInterface $updateCatalogProductSelectionCriteriaQuery,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $catalog = $this->findOneCatalogByIdQuery->execute($catalogId);

        if (null === $catalog) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        /** @var array<int, array{field: string, operator: string, value?: mixed}> $payload */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $violations = $this->validator->validate($payload, [
            new Criteria(),
        ]);

        if ($violations->count() > 0) {
            return new JsonResponse(
                [
                    'errors' => $this->normalizer->normalize($violations),
                    'message' => 'Criteria are not valid.',
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->updateCatalogProductSelectionCriteriaQuery->execute(
            $catalogId,
            $payload,
        );

        return new JsonResponse(null, 204);
    }
}

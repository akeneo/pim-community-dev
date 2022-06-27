<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogAction
{
    public function __construct(
        private ValidatorInterface $validator,
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
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

        /**
         * @var array{
         *      enabled: bool,
         *      product_selection_criteria: array<int, array{field: string, operator: string, value?: mixed}>,
         * } $payload
         */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $violations = $this->validator->validate($payload, $this->getPayloadConstraints());

        if ($violations->count() > 0) {
            return new JsonResponse(
                [
                    'errors' => $this->normalizer->normalize($violations),
                    'message' => 'Catalog is not valid.',
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->upsertCatalogQuery->execute(
            id: $catalogId,
            name: $catalog->getName(),
            ownerUsername: $catalog->getOwnerUsername(),
            enabled: $payload['enabled'],
        );

        $this->updateCatalogProductSelectionCriteriaQuery->execute(
            $catalogId,
            $payload['product_selection_criteria'],
        );

        return new JsonResponse(null, 204);
    }

    /**
     * @return array<Constraint>
     */
    private function getPayloadConstraints(): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                        new Assert\NotBlank(),
                    ]),
                    'product_selection_criteria' => [
                        new Assert\Type('array'),
                        new Assert\All(
                            new Assert\Collection([
                                'fields' => [
                                    'field' => [
                                        new Assert\Type('string'),
                                        new Assert\NotBlank(),
                                    ],
                                    'operator' => [
                                        new Assert\Type('string'),
                                        new Assert\NotBlank(),
                                    ],
                                    'value' => [
                                        new Assert\Optional(),
                                    ],
                                ],
                                'allowMissingFields' => false,
                                'allowExtraFields' => false,
                            ]),
                        ),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }
}

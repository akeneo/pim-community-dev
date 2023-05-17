<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\UpdateAttributeCommand\UpdateAttributeCommand;
use Akeneo\Category\Domain\Exceptions\ViolationsException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAttributeController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly CommandMessageBus $categoryCommandBus,
        private readonly NormalizerInterface $constraintViolationNormalizer,
    ) {
    }

    /**
     * @param string $attributeUuid We doesn't use the $templateUuid in the code, but we keep it for interface convention. It maintains explicit link between the attribute UUID with its template.
     */
    public function __invoke(Request $request, string $templateUuid, string $attributeUuid): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_product_category_template')) {
            throw new AccessDeniedException();
        }

        $data = $request->toArray();

        try {
            $command = UpdateAttributeCommand::create(
                attributeUuid: $attributeUuid,
                isRichTextArea: $data['isRichRextArea'],
            );
            $this->categoryCommandBus->dispatch($command);
        } catch (ViolationsException $violationsException) {
            $normalizedViolations = [];
            foreach ($violationsException->violations() as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['attribute' => $attributeUuid],
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}

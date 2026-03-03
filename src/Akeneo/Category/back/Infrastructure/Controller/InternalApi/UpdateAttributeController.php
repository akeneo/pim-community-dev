<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Command\UpdateAttributeCommand\UpdateAttributeCommand;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAttributeController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly CommandBus $commandBus,
    ) {
    }

    /**
     * @param string $attributeUuid We don't use the $templateUuid in the code, but we keep it for interface convention. It maintains explicit link between the attribute UUID with its template.
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
                isRichTextArea: $data['isRichTextArea'] ?? null,
                labels: $data['labels'] ?? null,
            );
            $this->commandBus->dispatch($command);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalize(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}

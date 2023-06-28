<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand\ReorderTemplateAttributesCommand;
use Akeneo\Category\Domain\Exception\TemplateNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReorderTemplateAttributesController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly CommandMessageBus $categoryCommandBus,
    ) {
    }

    public function __invoke(Request $request, string $templateUuid): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_product_category_template')) {
            throw new AccessDeniedException();
        }

        $attributeUuids = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $command = ReorderTemplateAttributesCommand::create(
            templateUuid: $templateUuid,
            attributeUuids: $attributeUuids,
        );

        try {
            $this->categoryCommandBus->dispatch($command);
        } catch (TemplateNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

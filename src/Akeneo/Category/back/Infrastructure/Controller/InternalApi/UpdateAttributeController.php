<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\UpdateAttributeCommand;
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
        private readonly CommandMessageBus $categoryCommandBus,
    ) {
    }

    public function __invoke(Request $request, string $templateUuid, string $attributeUuid): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_product_category_template')) {
            throw new AccessDeniedException();
        }

        $data = $request->toArray();

        $command = UpdateAttributeCommand::create(
            attributeUuid: $attributeUuid,
            isRichTextArea: $data['is_rich_text_area'],
        );
        $this->categoryCommandBus->dispatch($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}

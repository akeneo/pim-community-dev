<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeController
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

        $data = $request->toArray();

        try {
            $command = AddAttributeCommand::create(
                code: $data['code'],
                type: $data['type'],
                isScopable: $data['is_scopable'],
                isLocalizable: $data['is_localizable'],
                templateUuid: $templateUuid,
                locale: $data['locale'],
                label: $data['label'],
            );
            $this->categoryCommandBus->dispatch($command);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalizeDeprecated(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}

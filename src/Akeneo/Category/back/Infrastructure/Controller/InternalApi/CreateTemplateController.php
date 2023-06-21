<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommand;
use Akeneo\Category\Domain\Exception\CategoryTreeNotFoundException;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateTemplateController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly GetCategoryInterface $getCategory,
        private readonly CommandMessageBus $categoryCommandBus,
    ) {
    }

    public function __invoke(Request $request, int $categoryTreeId): Response
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_template') === false) {
            throw new AccessDeniedException();
        }

        $categoryTree = $this->getCategory->byId($categoryTreeId);
        if (null === $categoryTree) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $command = new CreateTemplateCommand(
                $categoryTree->getId(),
                $request->toArray(),
            );
            $this->categoryCommandBus->dispatch($command);
        } catch (ViolationsException $violationsException) {
            return new JsonResponse($violationsException->normalize(), Response::HTTP_BAD_REQUEST);
        } catch (CategoryTreeNotFoundException $exception) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}

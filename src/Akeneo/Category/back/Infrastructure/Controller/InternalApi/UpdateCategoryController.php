<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Application\Converter\StandardFormatToUserIntentsInterface;
use Akeneo\Category\Application\Filter\CategoryEditACLFilter;
use Akeneo\Category\Application\Filter\CategoryEditUserIntentFilter;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Infrastructure\Converter\InternalAPI\InternalAPIToStd;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type StandardInternalApi from InternalAPIToStd
 */
class UpdateCategoryController
{
    public function __construct(
        private CommandMessageBus $categoryCommandBus,
        private SecurityFacade $securityFacade,
        private ConverterInterface $internalApiToStandardConverter,
        private CategoryEditACLFilter $ACLFilter,
        private StandardFormatToUserIntentsInterface $standardFormatToUserIntents,
        private CategoryEditUserIntentFilter $categoryUserIntentFilter,
        private GetCategoryInterface $getCategory,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_edit') === false) {
            throw new AccessDeniedException();
        }

        $category = $this->getCategory->byId($id);
        if ($category === null) {
            throw new NotFoundHttpException('Category not found');
        }

        // Transform request to a user intent list
        $data = $request->toArray();
        /** @var  StandardInternalApi $formattedData */
        $formattedData = $this->internalApiToStandardConverter->convert($data);
        $filteredData = $this->ACLFilter->filterCollection($formattedData, 'category', []);
        $userIntents = $this->standardFormatToUserIntents->convert($filteredData);
        $filteredUserIntents = $this->categoryUserIntentFilter->filterCollection($userIntents);

        try {
            $command = UpsertCategoryCommand::create(
                (string)$category->getCode(),
                $filteredUserIntents
            );
            $this->categoryCommandBus->dispatch($command);
        } catch (ViolationsException $e) {
            //Todo: Handle violations exceptions when all stubbed services have been replaced by real ones
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $category = $this->getCategory->byId($id);
        if ($category === null) {
            throw new NotFoundHttpException('Category not found');
        }
        $normalizedCategory = $category->normalize();

        return new JsonResponse($normalizedCategory, Response::HTTP_OK);
    }
}

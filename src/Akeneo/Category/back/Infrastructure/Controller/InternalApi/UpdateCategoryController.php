<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Application\Converter\StandardFormatToUserIntentsInterface;
use Akeneo\Category\Application\Filter\CategoryEditAclFilter;
use Akeneo\Category\Application\Filter\CategoryEditUserIntentFilter;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
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
 * @phpstan-import-type StandardInternalApi from InternalApiToStd
 */
class UpdateCategoryController
{
    public function __construct(
        private readonly CommandMessageBus $categoryCommandBus,
        private readonly SecurityFacade $securityFacade,
        private readonly ConverterInterface $internalApiToStandardConverter,
        private readonly CategoryEditAclFilter $categoryEditAclFilter,
        private readonly StandardFormatToUserIntentsInterface $standardFormatToUserIntents,
        private readonly CategoryEditUserIntentFilter $categoryUserIntentFilter,
        private readonly GetCategoryInterface $getCategory,
        private readonly FindCategoryAdditionalPropertiesRegistry $findCategoryAdditionalPropertiesRegistry,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_edit') === false) {
            throw new AccessDeniedException();
        }

        $category = $this->getCategory->byId($id);
        $category = $this->findCategoryAdditionalPropertiesRegistry->forCategory($category);

        // Transform request to a user intent list
        $data = $request->toArray();
        /** @var StandardInternalApi $formattedData */
        $formattedData = $this->internalApiToStandardConverter->convert($data);
        $filteredData = $this->categoryEditAclFilter->filterCollection($formattedData);
        $userIntents = $this->standardFormatToUserIntents->convert($filteredData);
        $filteredUserIntents = $this->categoryUserIntentFilter->filterCollection($userIntents);

        try {
            $command = UpsertCategoryCommand::create(
                (string) $category->getCode(),
                $filteredUserIntents,
            );
            $this->categoryCommandBus->dispatch($command);
        } catch (ViolationsException $e) {
            // Todo: Handle violations exceptions when all stubbed services have been replaced by real ones
            // The data structure to be returned to the UI must allow to display the violation messages
            // next to the violating attribute
            // (so at minimum : a mapping from the attribute code to a i18n key for the error message)
            return new JsonResponse(
                [
                    'success' => false,
                    'errors' => [
                        'attributes' => [
                            [
                                'path' => ['attribute', 'somecode'],
                                'locale' => 'fr_FR', // optional
                                'message' => [
                                    'key' => 'i18n key for some constraint violation message, maybe with some {{a}} arguments',
                                    'args' => [
                                        'a' => 123,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $category = $this->getCategory->byId($id);
        $category = $this->findCategoryAdditionalPropertiesRegistry->forCategory($category);

        $normalizedCategory = $category->normalize();

        return new JsonResponse(['success' => true, 'category' => $normalizedCategory], Response::HTTP_OK);
    }
}

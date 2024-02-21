<?php

namespace Akeneo\Category\Infrastructure\Controller\ExternalApi;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetCategoryController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly GetCategoriesParametersBuilder $parametersBuilder,
        private readonly GetCategoriesInterface $getCategories,
    ) {
    }

    public function __invoke(Request $request, string $code): JsonResponse|Response
    {
        if ($this->securityFacade->isGranted('pim_api_category_list') === false) {
            throw new AccessDeniedException();
        }
        $searchFilters = ['code' => [['operator' => 'IN', 'value' => [$code]]]];
        $withEnrichedAttributes = $request->query->getBoolean('with_enriched_attributes');
        $withPosition = $request->query->getBoolean('with_position');

        try {
            $sqlParameters = $this->parametersBuilder->build(
                searchFilters: $searchFilters,
                limit: 1,
                offset: 0,
                withPosition: $withPosition,
                isEnrichedAttributes: $withEnrichedAttributes,
            );

            $externalApiCategory = current($this->getCategories->execute($sqlParameters));
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        if (!$externalApiCategory) {
            throw new NotFoundHttpException(sprintf('Category "%s" does not exist.', $code));
        }

        $normalizedCategory = $externalApiCategory->normalize($withPosition, $withEnrichedAttributes);

        return new JsonResponse($normalizedCategory);
    }
}

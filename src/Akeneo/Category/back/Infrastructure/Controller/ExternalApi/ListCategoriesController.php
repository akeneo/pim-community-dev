<?php

namespace Akeneo\Category\Infrastructure\Controller\ExternalApi;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListCategoriesController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly PaginatorInterface $paginator,
        private readonly ParameterValidatorInterface $parameterValidator,
        private readonly GetCategoriesParametersBuilder $parametersBuilder,
        private readonly GetCategoriesInterface $getCategories,
        private readonly array $apiConfiguration,
    ) {
    }

    public function __invoke(Request $request): JsonResponse|Response
    {
        if ($this->securityFacade->isGranted('pim_api_category_list') === false) {
            throw new AccessDeniedException();
        }
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'page' => 1,
            'limit' => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());
        try {
            $searchFilters = json_decode($queryParameters['search'] ?? '[]', true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException(sprintf('The search query parameter must be a valid JSON: %s', $e->getMessage()));
        }
        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $withEnrichedAttributes = $request->query->getBoolean('with_enriched_attributes');
        $withPosition = $request->query->getBoolean('with_position');

        try {
            $sqlParameters = $this->parametersBuilder->build(
                searchFilters: $searchFilters,
                limit: $queryParameters['limit'],
                offset: $offset,
                withPosition: $withPosition,
                isEnrichedAttributes: $withEnrichedAttributes,
            );
            $externalCategoriesApi = $this->getCategories->execute($sqlParameters);
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name' => 'pim_api_category_list',
            'item_route_name' => 'pim_api_category_get',
        ];

        $count = null;
        if ($request->query->getBoolean('with_count') === true) {
            $count = $this->getCategories->count($sqlParameters);
        }

        $normalizedCategories = [];
        foreach ($externalCategoriesApi as $externalCategory) {
            $normalizedCategories[] = $externalCategory->normalize($withPosition, $withEnrichedAttributes);
        }

        $paginatedCategories = $this->paginator->paginate(
            $normalizedCategories,
            $parameters,
            $count,
        );

        return new JsonResponse($paginatedCategories);
    }
}

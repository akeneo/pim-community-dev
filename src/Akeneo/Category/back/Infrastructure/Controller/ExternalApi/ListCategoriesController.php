<?php

namespace Akeneo\Category\Infrastructure\Controller\ExternalApi;

use Akeneo\Category\Application\Handler\GetPositionInterface;
use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Akeneo\Category\ServiceApi\ExternalApiCategory;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListCategoriesController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ParameterValidatorInterface $parameterValidator,
        private readonly FeatureFlags $featureFlags,
        private readonly GetCategoriesParametersBuilder $parametersBuilder,
        private readonly GetCategoriesInterface $getCategories,
        private readonly GetPositionInterface $getPosition,
        private readonly array $apiConfiguration,
    ) {
    }

    /**
     * @AclAncestor("pim_api_category_list")
     */
    public function __invoke(Request $request): JsonResponse|Response
    {
        if (!$this->featureFlags->isEnabled('enriched_category')) {
            return $this->forward(
                controller: 'pim_api.controller.category::listAction',
                query: $request->query->all(),
            );
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
        try {
            $sqlParameters = $this->parametersBuilder->build(
                $searchFilters,
                $queryParameters['limit'],
                $offset,
                $withEnrichedAttributes,
            );
            $categories = $this->getCategories->execute($sqlParameters);
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
        foreach ($categories as $category) {
            $categoryApi = ExternalApiCategory::fromDomainModel($category);
            $withPosition = $request->query->getBoolean('with_position');
            if ($withPosition) {
                $categoryApi->setPosition(($this->getPosition)($category));
            }
            $normalizedCategories[] = $categoryApi->normalize($withPosition, $withEnrichedAttributes);
        }

        $paginatedCategories = $this->paginator->paginate(
            $normalizedCategories,
            $parameters,
            $count,
        );

        return new JsonResponse($paginatedCategories);
    }
}

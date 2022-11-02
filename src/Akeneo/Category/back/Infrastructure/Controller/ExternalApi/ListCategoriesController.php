<?php

namespace Akeneo\Category\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListCategoriesController
{
    public function __construct(
        private ApiResourceRepositoryInterface $repository,
        private NormalizerInterface $normalizer,
        private PaginatorInterface $paginator,
        private ParameterValidatorInterface $parameterValidator,
        private FeatureFlags $featureFlags,
        private array $apiConfiguration
    ) {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     *
     * @AclAncestor("pim_api_category_list")
     */
    public function __invoke(Request $request)
    {
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'page'       => 1,
            'limit'      => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());
        $searchFilters = json_decode($queryParameters['search'] ?? '[]', true);
        if (null === $searchFilters) {
            throw new BadRequestHttpException('The search query parameter must be a valid JSON.');
        }

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $order = ['root' => 'ASC', 'left' => 'ASC'];
        try {
            $categories = [];

            if (!$this->featureFlags->isEnabled('enriched_category')) {
                $categories = $this->repository->searchAfterOffset(
                    $searchFilters,
                    $order,
                    $queryParameters['limit'],
                    $offset
                );
            }

            // TODO: Get the list of categories by the new ServiceApi
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $parameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pim_api_category_list',
            'item_route_name'     => 'pim_api_category_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count($searchFilters) : null;

        $paginatedCategories = $this->paginator->paginate(
            $this->normalizer->normalize(
                $categories,
                'external_api',
                ['with_position' => $request->query->getBoolean('with_position')]
            ),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedCategories);
    }
}

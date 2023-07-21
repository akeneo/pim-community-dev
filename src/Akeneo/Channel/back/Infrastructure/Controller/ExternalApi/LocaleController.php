<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocaleController
{
    private const AUTHORIZED_FIELD_FILTERS = ['enabled'];

    public function __construct(
        private ApiResourceRepositoryInterface $repository,
        private NormalizerInterface $normalizer,
        private PaginatorInterface $paginator,
        private ParameterValidatorInterface $parameterValidator,
        private QueryParametersCheckerInterface $queryParametersChecker,
        private array $apiConfiguration,
        private SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function getAction(Request $request, string $code): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_api_locale_list')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        $locale = $this->repository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist.', $code));
        }

        $localeApi = $this->normalizer->normalize($locale, 'external_api');

        return new JsonResponse($localeApi);
    }

    #[OA\Get(
        path: '/api/rest/v1/locales',
        operationId: 'get_locales',
        description: 'This endpoint allows you to get a list of locales. Locales are paginated and sorted by code.',
        summary: 'Get a list of locales',
        security: [
            ['bearerToken' => []],
        ],
        tags: ['Locale'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Filter locales. For now, only the `enabled` filter is available.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: '{"enabled":[{"operator":"=","value":true}]}'
                )
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Maximum number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 10
                )
            ),
            new OA\Parameter(
                name: 'with_count',
                description: 'Whether or not to count the total of items',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    example: false
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Return locales paginated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: '_links',
                            ref: '#/components/schemas/_links',
                        ),
                        new OA\Property(
                            property: 'current_page',
                            description: 'Current page number',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: '_embedded',
                            ref: '#/components/schemas/_embedded_locale'
                        )
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(
                ref: '#/components/responses/401',
                response: '401'
            ),
            new OA\Response(
                ref: '#/components/responses/403',
                response: '403'
            ),
            new OA\Response(
                ref: '#/components/responses/406',
                response: '406'
            ),
        ]
    )]
    public function listAction(Request $request): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_api_locale_list')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        $searchCriterias = $this->validateSearchCriterias($request);
        $criterias = $this->prepareSearchCriterias($searchCriterias);

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

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $locales = $this->repository->searchAfterOffset(
            $criterias,
            ['code' => 'ASC'],
            $queryParameters['limit'],
            $offset
        );

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pim_api_locale_list',
            'item_route_name'  => 'pim_api_locale_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count($criterias) : null;
        $paginatedLocales = $this->paginator->paginate(
            $this->normalizer->normalize($locales, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedLocales);
    }

    /**
     * Prepares criterias from search parameters
     * It throws exceptions if search parameters are not correctly filled
     * Only activated = filter is authorized today
     */
    private function validateSearchCriterias(Request $request): array
    {
        if (!$request->query->has('search')) {
            return [];
        }

        $searchString = $request->query->get('search', '');
        $searchParameters = $this->queryParametersChecker->checkCriterionParameters($searchString);

        foreach ($searchParameters as $searchKey => $searchParameter) {
            foreach ($searchParameter as $searchOperator) {
                if (!in_array($searchKey, self::AUTHORIZED_FIELD_FILTERS)
                    || '=' !== $searchOperator['operator']) {
                    throw new UnprocessableEntityHttpException(
                        sprintf(
                            'Filter on property "%s" is not supported or does not support operator "%s".',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }

                if (!isset($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Value is missing for the property "%s".', $searchKey)
                    );
                }

                if (!is_bool($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf(
                            'Filter "%s" with operator "%s" expects a boolean value.',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }
            }
        }

        return $searchParameters;
    }

    /**
     * Prepares search criterias
     * For now, only enabled filter with operator "=" are managed
     * Value is a boolean
     */
    private function prepareSearchCriterias(array $searchParameters): array
    {
        if (empty($searchParameters)) {
            return [];
        }

        return [
            'activated' => $searchParameters['enabled'][0]['value']
        ];
    }
}

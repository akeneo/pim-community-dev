<?php

namespace Akeneo\Channel\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Bundle\ApiBundle\Doctrine\ORM\Repository\ApiResourceRepository;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CurrencyController
{
    public function __construct(
        private ApiResourceRepository $repository,
        private NormalizerInterface $normalizer,
        private ParameterValidatorInterface $parameterValidator,
        private PaginatorInterface $paginator,
        private array $apiConfiguration,
        private SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function getAction(Request $request, string $code): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_api_currency_list')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        $currency = $this->repository->findOneByIdentifier($code);
        if (null === $currency) {
            throw new NotFoundHttpException(sprintf('Currency "%s" does not exist.', $code));
        }

        $currencyApi = $this->normalizer->normalize($currency, 'external_api');

        return new JsonResponse($currencyApi);
    }

    public function listAction(Request $request): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_api_currency_list')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

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
        $currencies = $this->repository->searchAfterOffset(
            [],
            ['code' => 'ASC'],
            $queryParameters['limit'],
            $offset
        );

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pim_api_currency_list',
            'item_route_name'  => 'pim_api_currency_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count() : null;
        $paginatedCurrencies = $this->paginator->paginate(
            $this->normalizer->normalize($currencies, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedCurrencies);
    }
}

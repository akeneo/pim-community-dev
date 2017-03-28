<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
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
class ChannelController
{
    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param PaginatorInterface             $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_channel_list")
     */
    public function getAction(Request $request, $code)
    {
        $channel = $this->repository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new NotFoundHttpException(sprintf('Channel "%s" does not exist.', $code));
        }

        $channelApi = $this->normalizer->normalize($channel, 'external_api');

        return new JsonResponse($channelApi);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_channel_list")
     */
    public function listAction(Request $request)
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

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $channels = $this->repository->searchAfterOffset([], ['code' => 'ASC'], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pim_api_channel_list',
            'item_route_name'  => 'pim_api_channel_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count() : null;
        $paginatedChannels = $this->paginator->paginate(
            $this->normalizer->normalize($channels, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedChannels);
    }
}

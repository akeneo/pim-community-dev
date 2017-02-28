<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
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

    /** @var HalPaginator */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param HalPaginator                   $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        HalPaginator $paginator,
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
        $queryParameters = [
            'page'  => $request->query->get('page', 1),
            'limit' => $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default'])
        ];

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $count = $this->repository->count([]);
        $channels = $this->repository->searchAfterOffset([], ['code' => 'ASC'], $queryParameters['limit'], $offset);

        $channelsApi = $this->normalizer->normalize($channels, 'external_api');

        $paginatedChannels = $this->paginator->paginate(
            $channelsApi,
            array_merge($request->query->all(), $queryParameters),
            $count,
            'pim_api_channel_list',
            'pim_api_channel_get',
            'code'
        );

        return new JsonResponse($paginatedChannels);
    }
}

<?php

namespace Akeneo\Channel\Bundle\Controller\ExternalApi;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var RouterInterface */
    protected $router;

    /** @var SaverInterface */
    protected $saver;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param PaginatorInterface             $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param SimpleFactoryInterface         $factory
     * @param ObjectUpdaterInterface         $updater
     * @param ValidatorInterface             $validator
     * @param RouterInterface                $router
     * @param SaverInterface                 $saver
     * @param StreamResourceResponse         $partialUpdateStreamResource
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        RouterInterface $router,
        SaverInterface $saver,
        StreamResourceResponse $partialUpdateStreamResource,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->router = $router;
        $this->saver = $saver;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
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

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_channel_edit")
     */
    public function createAction(Request $request)
    {
        $data = $this->getDecodedContent($request->getContent());

        $channel = $this->factory->create();

        if (isset($data['conversion_units']) && is_array($data['conversion_units'])) {
            $data['conversion_units'] = $this->mergeAndFilterConversionUnits($channel, $data);
        }

        $this->updateChannel($channel, $data, 'post_channels');
        $this->validateChannel($channel);

        $this->saver->save($channel);

        $response = $this->getResponse($channel, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_channel_edit")
     */
    public function partialUpdateAction(Request $request, $code)
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $channel = $this->repository->findOneByIdentifier($code);

        if (null === $channel) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $channel = $this->factory->create();
        }

        if (isset($data['conversion_units']) && is_array($data['conversion_units'])) {
            $data['conversion_units'] = $this->mergeAndFilterConversionUnits($channel, $data);
        }

        $this->updateChannel($channel, $data, 'patch_channels__code_');
        $this->validateChannel($channel);

        $this->saver->save($channel);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($channel, $status);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_channel_edit")
     */
    public function partialUpdateListAction(Request $request)
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

        return $response;
    }

    /**
     * `conversion_units` are not well exposed through the api, on the api side it is an object while in ChannelInterface it is an array.
     * To follow the API merge rules on object https://api-staging.akeneo.com/documentation/update.html#patch-rules,
     * we are forced to process data before updating them, we cannot change this behavior everywhere to avoid BC breaks.
     *
     * @param ChannelInterface $channel
     * @param array            $data
     *
     * @return array
     */
    private function mergeAndFilterConversionUnits($channel, $data): array
    {
        return array_filter(
            array_merge($channel->getConversionUnits(), $data['conversion_units']),
            function ($value) {
                return null !== $value && '' !== $value;
            }
        );
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function getDecodedContent($content)
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Update a channel. It throws an error 422 if a problem occurred during the update.
     *
     * @param ChannelInterface $channel
     * @param array            $data
     * @param string           $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateChannel(ChannelInterface $channel, array $data, $anchor)
    {
        try {
            $this->updater->update($channel, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate a channel. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param \Akeneo\Channel\Component\Model\ChannelInterface $channel
     *
     * @throws ViolationHttpException
     */
    protected function validateChannel(ChannelInterface $channel)
    {
        $violations = $this->validator->validate($channel);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param \Akeneo\Channel\Component\Model\ChannelInterface $channel
     * @param int                                              $status
     *
     * @return Response
     */
    protected function getResponse(ChannelInterface $channel, $status)
    {
        $response = new Response(null, $status);
        $url = $this->router->generate(
            'pim_api_channel_get',
            ['code' => $channel->getCode()],
            Router::ABSOLUTE_URL
        );
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * Throw an exception if the code provided in the url and the code provided in the request body
     * are not equals when creating a channel with a PATCH method.
     *
     * The code in the request body is optional when we create a resource with PATCH.
     *
     * @param string $code code provided in the url
     * @param array  $data body of the request already decoded
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency($code, array $data)
    {
        if (array_key_exists('code', $data) && $code !== $data['code']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The code "%s" provided in the request body must match the code "%s" provided in the url.',
                    $data['code'],
                    $code
                )
            );
        }
    }
}

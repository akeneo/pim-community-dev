<?php

namespace Akeneo\Channel\Infrastructure\Controller\ExternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
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
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    public function __construct(
        private ApiResourceRepositoryInterface $repository,
        private NormalizerInterface $normalizer,
        private PaginatorInterface $paginator,
        private ParameterValidatorInterface $parameterValidator,
        private SimpleFactoryInterface $factory,
        private ObjectUpdaterInterface $updater,
        private ValidatorInterface $validator,
        private RouterInterface $router,
        private SaverInterface $saver,
        private StreamResourceResponse $partialUpdateStreamResource,
        private array $apiConfiguration,
        private SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function getAction(Request $request, string $code): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_api_channel_list')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        $channel = $this->repository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new NotFoundHttpException(sprintf('Channel "%s" does not exist.', $code));
        }

        $channelApi = $this->normalizer->normalize($channel, 'external_api');

        return new JsonResponse($channelApi);
    }

    public function listAction(Request $request): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_api_channel_list')) {
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

    public function createAction(Request $request): Response
    {
        if (!$this->securityFacade->isGranted('pim_api_channel_edit')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

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

    public function partialUpdateAction(Request $request, string $code): Response
    {
        if (!$this->securityFacade->isGranted('pim_api_channel_edit')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

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

    public function partialUpdateListAction(Request $request): Response
    {
        if (!$this->securityFacade->isGranted('pim_api_channel_edit')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

        return $response;
    }

    /**
     * `conversion_units` are not well exposed through the api, on the api side it is an object while in ChannelInterface it is an array.
     * To follow the API merge rules on object https://api-staging.akeneo.com/documentation/update.html#patch-rules,
     * we are forced to process data before updating them, we cannot change this behavior everywhere to avoid BC breaks.
     */
    private function mergeAndFilterConversionUnits(ChannelInterface $channel, array $data): array
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
     */
    private function getDecodedContent(string $content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Update a channel. It throws an error 422 if a problem occurred during the update.
     */
    private function updateChannel(ChannelInterface $channel, array $data, string $anchor): void
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
     */
    private function validateChannel(ChannelInterface $channel): void
    {
        $violations = $this->validator->validate($channel);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     */
    private function getResponse(ChannelInterface $channel, int $status): Response
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
     */
    private function validateCodeConsistency(string $code, array $data): void
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

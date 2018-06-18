<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
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
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController
{
    /** @var AttributeRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var  ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /** @var RouterInterface */
    protected $router;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param NormalizerInterface          $normalizer
     * @param SimpleFactoryInterface       $factory
     * @param ObjectUpdaterInterface       $updater
     * @param ValidatorInterface           $validator
     * @param SaverInterface               $saver
     * @param RouterInterface              $router
     * @param PaginatorInterface           $paginator
     * @param ParameterValidatorInterface  $parameterValidator
     * @param StreamResourceResponse       $partialUpdateStreamResource
     * @param array                        $apiConfiguration
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        StreamResourceResponse $partialUpdateStreamResource,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->router = $router;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
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
     * @AclAncestor("pim_api_attribute_list")
     */
    public function getAction(Request $request, $code)
    {
        $attribute = $this->repository->findOneByIdentifier($code);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist.', $code));
        }

        $attributeApi = $this->normalizer->normalize($attribute, 'external_api');

        return new JsonResponse($attributeApi);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_attribute_list")
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
        $attributes = $this->repository->searchAfterOffset([], ['code' => 'ASC'], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pim_api_attribute_list',
            'item_route_name'     => 'pim_api_attribute_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count() : null;
        $paginatedAttributes = $this->paginator->paginate(
            $this->normalizer->normalize($attributes, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedAttributes);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_attribute_edit")
     */
    public function createAction(Request $request)
    {
        $data = $this->getDecodedContent($request->getContent());

        $attribute = $this->factory->create();
        $this->updateAttribute($attribute, $data, 'post_attributes');
        $this->validateAttribute($attribute);

        $this->saver->save($attribute);

        $response = $this->getResponse($attribute, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_attribute_edit")
     */
    public function partialUpdateListAction(Request $request)
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     *
     * @AclAncestor("pim_api_attribute_edit")
     */
    public function partialUpdateAction(Request $request, $code)
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $attribute = $this->repository->findOneByIdentifier($code);

        if (null === $attribute) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $attribute = $this->factory->create();
        }

        $this->updateAttribute($attribute, $data, 'patch_attributes__code_');
        $this->validateAttribute($attribute);

        $this->saver->save($attribute);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($attribute, $status);

        return $response;
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
     * Update an attribute. It throws an error 422 if a problem occurred during the update.
     *
     * @param AttributeInterface $attribute
     * @param array              $data
     * @param string             $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateAttribute(AttributeInterface $attribute, array $data, $anchor)
    {
        try {
            $this->updater->update($attribute, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate an attribute. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param AttributeInterface $attribute
     *
     * @throws ViolationHttpException
     */
    protected function validateAttribute(AttributeInterface $attribute)
    {
        $violations = $this->validator->validate($attribute);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Throw an exception if the code provided in the url and the code provided in the request body
     * are not equals when creating a category with a PATCH method.
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

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param AttributeInterface $attribute
     * @param int                $status
     *
     * @return Response
     */
    protected function getResponse(AttributeInterface $attribute, $status)
    {
        $response = new Response(null, $status);
        $url = $this->router->generate(
            'pim_api_attribute_get',
            ['code' => $attribute->getCode()],
            Router::ABSOLUTE_URL
        );
        $response->headers->set('Location', $url);

        return $response;
    }
}

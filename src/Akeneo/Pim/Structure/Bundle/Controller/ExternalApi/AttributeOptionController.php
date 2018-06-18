<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
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
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ApiResourceRepositoryInterface */
    protected $attributeOptionsRepository;

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

    /** @var array */
    protected $supportedAttributeTypes;

    /**
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param ApiResourceRepositoryInterface $attributeOptionsRepository
     * @param NormalizerInterface            $normalizer
     * @param SimpleFactoryInterface         $factory
     * @param ObjectUpdaterInterface         $updater
     * @param ValidatorInterface             $validator
     * @param SaverInterface                 $saver
     * @param RouterInterface                $router
     * @param PaginatorInterface             $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param StreamResourceResponse         $partialUpdateStreamResource
     * @param array                          $apiConfiguration
     * @param array                          $supportedAttributeTypes
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ApiResourceRepositoryInterface $attributeOptionsRepository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        StreamResourceResponse $partialUpdateStreamResource,
        array $apiConfiguration,
        array $supportedAttributeTypes
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionsRepository = $attributeOptionsRepository;
        $this->normalizer = $normalizer;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->router = $router;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->apiConfiguration = $apiConfiguration;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_attribute_option_list")
     */
    public function getAction(Request $request, $attributeCode, $code)
    {
        $attribute = $this->getAttribute($attributeCode);
        $this->isAttributeSupportingOptions($attribute);

        $attributeOption = $this->attributeOptionsRepository->findOneByIdentifier($attributeCode . '.' . $code);
        if (null === $attributeOption) {
            throw new NotFoundHttpException(
                sprintf(
                    'Attribute option "%s" does not exist or is not an option of the attribute "%s".',
                    $code,
                    $attributeCode
                )
            );
        }

        $attributeOptionApi = $this->normalizer->normalize($attributeOption, 'external_api');

        return new JsonResponse($attributeOptionApi);
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     *
     * @throws HttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_attribute_option_list")
     */
    public function listAction(Request $request, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $this->isAttributeSupportingOptions($attribute);

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

        $criteria['attribute'] = $attribute->getId();

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $attributeOptions = $this->attributeOptionsRepository->searchAfterOffset(
            $criteria,
            ['code' => 'ASC'],
            $queryParameters['limit'],
            $offset
        );

        $parameters = [
            'query_parameters'    => $queryParameters,
            'uri_parameters'      => ['attributeCode' => $attributeCode],
            'list_route_name'     => 'pim_api_attribute_option_list',
            'item_route_name'     => 'pim_api_attribute_option_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->attributeOptionsRepository->count($criteria) : null;
        $paginatedAttributeOptions = $this->paginator->paginate(
            $this->normalizer->normalize($attributeOptions, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedAttributeOptions);
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     *
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_attribute_option_edit")
     */
    public function createAction(Request $request, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);

        $data = $this->getDecodedContent($request->getContent());

        $this->validateCodeConsistency($attributeCode, null, $data, true);

        $data['attribute'] = $attributeCode;
        $attributeOption = $this->factory->create();
        $this->updateAttributeOption($attributeOption, $data, 'post_attributes__attribute_code__options');
        $this->validateAttributeOption($attributeOption);

        $this->saver->save($attributeOption);

        $response = $this->getResponse($attribute, $attributeOption, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     * @param string  $code
     *
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_attribute_option_edit")
     */
    public function partialUpdateAction(Request $request, $attributeCode, $code)
    {
        $attribute = $this->getAttribute($attributeCode);

        $data = $this->getDecodedContent($request->getContent());

        $attributeOption = $this->attributeOptionsRepository->findOneByIdentifier($attributeCode . '.' . $code);
        $isCreation = null === $attributeOption;

        $this->validateCodeConsistency($attributeCode, $code, $data, $isCreation);

        if ($isCreation) {
            $attributeOption = $this->factory->create();
        }

        $data['attribute'] = array_key_exists('attribute', $data) ? $data['attribute'] : $attributeCode;
        $data['code'] = array_key_exists('code', $data) ? $data['code'] : $code;
        $this->updateAttributeOption($attributeOption, $data, 'patch_attributes__attribute_code__options__code_');
        $this->validateAttributeOption($attributeOption);

        $this->saver->save($attributeOption);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($attribute, $attributeOption, $status);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     *
     * @return Response
     *
     * @AclAncestor("pim_api_attribute_option_edit")
     */
    public function partialUpdateListAction(Request $request, string $attributeCode): Response
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource, ['attributeCode' => $attributeCode]);

        return $response;
    }

    /**
     * Return an attribute. Throw an exception if attribute doesn't exist.
     *
     * @param string $attributeCode
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function getAttribute($attributeCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist.', $attributeCode));
        }

        return $attribute;
    }

    /**
     * Verify if an attribute supports options.
     *
     * @param AttributeInterface $attribute
     *
     * @throws NotFoundHttpException
     */
    protected function isAttributeSupportingOptions(AttributeInterface $attribute)
    {
        $attributeType = $attribute->getType();
        if (!in_array($attributeType, $this->supportedAttributeTypes)) {
            throw new NotFoundHttpException(
                sprintf(
                    'Attribute "%s" does not support options. Only attributes of type "%s" support options.',
                    $attribute->getCode(),
                    implode('", "', $this->supportedAttributeTypes)
                )
            );
        }
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
     * Update an attribute option. It throws an error 422 if a problem occurred during the update.
     *
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $data
     * @param string                   $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateAttributeOption(AttributeOptionInterface $attributeOption, $data, $anchor)
    {
        try {
            $this->updater->update($attributeOption, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate an attribute option. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param AttributeOptionInterface $attributeOption
     *
     * @throws ViolationHttpException
     */
    protected function validateAttributeOption(AttributeOptionInterface $attributeOption)
    {
        $violations = $this->validator->validate($attributeOption);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param AttributeInterface       $attribute
     * @param AttributeOptionInterface $attributeOption
     * @param int                      $status
     *
     * @return Response
     */
    protected function getResponse(AttributeInterface $attribute, AttributeOptionInterface $attributeOption, $status)
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_attribute_option_get',
            [
                'attributeCode' => $attribute->getCode(),
                'code'    => $attributeOption->getCode(),
            ],
            Router::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Throw an exception if the attribute code and the option code provided in the url don't match
     * attribute code and option code provided in the body.
     *
     * Attribute code and option code are optionals in the body when creating or updating a resource with a PATCH,
     * because they are already provided in the url.
     *
     * Option code is mandatory in the body when creating a resource with a POST, because it is not provided in the url.
     *
     * When it's a creation, attribute code and option code provided in the url should match those provided in the body.
     *
     * @param string      $attributeCode attribute code provided in the url
     * @param string|null $optionCode    option code provided in the url (in PATCH), null otherwise (in POST)
     * @param array       $data          body of the request already decoded
     * @param boolean     $isCreation    true if it's a creation, false if it's an update
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency($attributeCode, $optionCode, array $data, $isCreation)
    {
        if ($isCreation && array_key_exists('attribute', $data) && $attributeCode !== $data['attribute']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The attribute code "%s" provided in the request body must match the attribute code "%s" provided in the url.',
                    $data['attribute'],
                    $attributeCode
                )
            );
        }

        if ($isCreation && null !== $optionCode && array_key_exists('code', $data) && $optionCode !== $data['code']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The option code "%s" provided in the request body must match the option code "%s" provided in the url.',
                    $data['code'],
                    $optionCode
                )
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantController
{
    /** @var ApiResourceRepositoryInterface */
    protected $familyRepository;

    /** @var ApiResourceRepositoryInterface */
    protected $familyVariantRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var  ValidatorInterface */
    protected $validator;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RouterInterface */
    protected $router;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $familyRepository
     * @param ApiResourceRepositoryInterface $familyVariantRepository
     * @param NormalizerInterface            $normalizer
     * @param PaginatorInterface             $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param ValidatorInterface             $validator
     * @param SimpleFactoryInterface         $factory
     * @param ObjectUpdaterInterface         $updater
     * @param SaverInterface                 $saver
     * @param RouterInterface                $router
     * @param StreamResourceResponse         $partialUpdateStreamResource
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $familyRepository,
        ApiResourceRepositoryInterface $familyVariantRepository,
        NormalizerInterface $normalizer,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        ValidatorInterface $validator,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RouterInterface $router,
        StreamResourceResponse $partialUpdateStreamResource,
        array $apiConfiguration
    ) {
        $this->familyRepository = $familyRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->validator = $validator;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->router = $router;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_family_variant_list")
     */
    public function getAction(Request $request, string $familyCode, string $code): JsonResponse
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $familyCode));
        }

        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($code);
        if (null === $familyVariant || $familyVariant->getFamily()->getCode() !== $familyCode) {
            throw new NotFoundHttpException(
                sprintf(
                    'Family variant "%s" does not exist or is not a variant of the family "%s".',
                    $code,
                    $familyCode
                )
            );
        }

        $familyVariantApi = $this->normalizer->normalize($familyVariant, 'external_api');

        return new JsonResponse($familyVariantApi);
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_family_variant_list")
     */
    public function listAction(Request $request, string $familyCode): JsonResponse
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $familyCode));
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

        $criteria['family'] = $family->getId();

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $familyVariants = $this->familyVariantRepository->searchAfterOffset($criteria, ['code' =>'ASC'], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters'    => $queryParameters,
            'uri_parameters'      => ['familyCode' => $familyCode],
            'list_route_name'     => 'pim_api_family_variant_list',
            'item_route_name'     => 'pim_api_family_variant_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->familyVariantRepository->count($criteria) : null;
        $paginatedFamilies = $this->paginator->paginate(
            $this->normalizer->normalize($familyVariants, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedFamilies);
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_family_variant_edit")
     */
    public function createAction(Request $request, string $familyCode): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $familyVariant = $this->factory->create();
        $this->updateFamilyVariant($familyVariant, $data, $familyCode, 'post_families__family_code__variants');
        $this->validateFamilyVariant($familyVariant);

        $this->saver->save($familyVariant);

        $response = $this->getResponse($familyVariant, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     * @param string  $code
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_family_variant_edit")
     */
    public function partialUpdateAction(Request $request, string $familyCode, string $code): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($code);

        if (null === $familyVariant) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $familyVariant = $this->factory->create();
        }

        $this->updateFamilyVariant($familyVariant, $data, $familyCode, 'patch_families__family_code__variants__code__');
        $this->validateFamilyVariant($familyVariant);

        $this->saver->save($familyVariant);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($familyVariant, $status);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_family_variant_edit")
     */
    public function partialUpdateListAction(Request $request, string $familyCode): Response
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource, ['familyCode' => $familyCode]);

        return $response;
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param FamilyVariantInterface $familyVariant
     * @param string                 $status
     *
     * @return Response
     */
    protected function getResponse(FamilyVariantInterface $familyVariant, $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_family_variant_get',
            ['familyCode' => $familyVariant->getFamily()->getCode(), 'code' => $familyVariant->getCode()],
            Router::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Update a family variant. It throws an error 422 if a problem occurred during the update.
     *
     * @param FamilyVariantInterface $familyVariant family variant to update
     * @param array                  $data          data of the request already decoded, it should be the standard format
     * @param string                 $familyCode
     * @param string                 $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateFamilyVariant(
        FamilyVariantInterface $familyVariant,
        array $data,
        string $familyCode,
        string $anchor
    ): void {
        try {
            $this->updater->update($familyVariant, $data, ['familyCode' => $familyCode]);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate a family variant. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param FamilyVariantInterface $familyVariant
     *
     * @throws ViolationHttpException
     */
    protected function validateFamilyVariant(FamilyVariantInterface $familyVariant): void
    {
        $violations = $this->validator->validate($familyVariant);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
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
    protected function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Throw an exception if the code provided in the url and the code provided in the request body
     * are not equals when creating a family with a PATCH method.
     *
     * The code in the request body is optional when we create a resource with PATCH.
     *
     * @param string $familyVariantCode code provided in the url
     * @param array  $data              body of the request already decoded
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency($familyVariantCode, array $data)
    {
        if (array_key_exists('code', $data) && $familyVariantCode !== $data['code']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The code "%s" provided in the request body must match the code "%s" provided in the url.',
                    $data['code'],
                    $familyVariantCode
                )
            );
        }
    }
}

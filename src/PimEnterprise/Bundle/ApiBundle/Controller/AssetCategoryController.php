<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Gedmo\Exception\UnexpectedValueException;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
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
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class AssetCategoryController
{
    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

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

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param SimpleFactoryInterface         $factory
     * @param ObjectUpdaterInterface         $updater
     * @param ValidatorInterface             $validator
     * @param SaverInterface                 $saver
     * @param RouterInterface                $router
     * @param ParameterValidatorInterface    $parameterValidator
     * @param PaginatorInterface             $paginator
     * @param StreamResourceResponse         $partialUpdateStreamResource
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
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
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_list")
     */
    public function getAction(Request $request, string $code): Response
    {
        $assetCategory = $this->repository->findOneByIdentifier($code);
        if (null === $assetCategory) {
            throw new NotFoundHttpException(sprintf('Asset category "%s" does not exist.', $code));
        }

        $assetCategoryApi = $this->normalizer->normalize($assetCategory, 'external_api');

        return new JsonResponse($assetCategoryApi);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_list")
     */
    public function listAction(Request $request): Response
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
        $order = ['root' => 'ASC', 'left' => 'ASC'];
        $assetCategories = $this->repository->searchAfterOffset([], $order, $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pimee_api_asset_category_list',
            'item_route_name'  => 'pimee_api_asset_category_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count() : null;
        $paginatedCategories = $this->paginator->paginate(
            $this->normalizer->normalize($assetCategories, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedCategories);
    }
    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_edit")
     */
    public function createAction(Request $request): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $assetCategory = $this->factory->create();
        $this->updateCategory($assetCategory, $data, 'post_asset_categories');
        $this->validateCategory($assetCategory);

        $this->saver->save($assetCategory);

        $response = $this->getResponse($assetCategory, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_edit")
     */
    public function partialUpdateAction(Request $request, string $code): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $assetCategory = $this->repository->findOneByIdentifier($code);

        if (null === $assetCategory) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $assetCategory = $this->factory->create();
        }

        $this->updateCategory($assetCategory, $data, 'patch_asset_categories__code_');
        $this->validateCategory($assetCategory, $data);

        try {
            $this->saver->save($assetCategory);
        } catch (UnexpectedValueException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($assetCategory, $status);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_edit")
     */
    public function partialUpdateListAction(Request $request): Response
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

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
    protected function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Update a category. It throws an error 422 if a problem occurred during the update.
     *
     * @param CategoryInterface $category category to update
     * @param array             $data     data of the request already decoded
     * @param string            $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateCategory(CategoryInterface $category, array $data, $anchor): void
    {
        try {
            $this->updater->update($category, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate a category. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param CategoryInterface $category
     *
     * @throws ViolationHttpException
     */
    protected function validateCategory(CategoryInterface $category): void
    {
        $violations = $this->validator->validate($category);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param CategoryInterface $category
     * @param string            $status
     *
     * @return Response
     */
    protected function getResponse(CategoryInterface $category, $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pimee_api_asset_category_get',
            ['code' => $category->getCode()],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
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
    protected function validateCodeConsistency(string $code, array $data): void
    {
        if (isset($data['code']) && $code !== $data['code']) {
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

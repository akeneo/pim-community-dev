<?php

namespace Akeneo\Category\Infrastructure\ExternalApi;

use Akeneo\Category\API\Command\CreateCategoryCommand;
use Akeneo\Category\API\CommandBus;
use Akeneo\Category\API\Query\GetCategory;
use Akeneo\Category\Domain\Exception\InvalidPropertyException;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
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
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryController
{
    public function __construct(
        protected ApiResourceRepositoryInterface $repository,
        protected NormalizerInterface $normalizer,
        protected SimpleFactoryInterface $factory,
        protected ObjectUpdaterInterface $updater,
        protected ValidatorInterface $validator,
        protected SaverInterface $saver,
        protected RouterInterface $router,
        protected PaginatorInterface $paginator,
        protected ParameterValidatorInterface $parameterValidator,
        protected StreamResourceResponse $partialUpdateStreamResource,
        protected array $apiConfiguration,
        protected CommandBus $messageBus,
        private GetCategory $getCategory,
        private CategoryNormalizer $categoryNormalizer,
    ) {
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_category_list")
     */
    public function getAction(Request $request, $code)
    {
        $category = $this->getCategory->byCode($code);
        if (null === $category) {
            throw new NotFoundHttpException(sprintf('Category "%s" does not exist.', $code));
        }

        $categoryApi = $this->categoryNormalizer->normalize($category);

        return new JsonResponse($categoryApi);
    }

    /**
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_category_list")
     */
    public function listAction(Request $request)
    {
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'page' => 1,
            'limit' => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());
        $searchFilters = json_decode($queryParameters['search'] ?? '[]', true);
        if (null === $searchFilters) {
            throw new BadRequestHttpException('The search query parameter must be a valid JSON.');
        }

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $order = ['root' => 'ASC', 'left' => 'ASC'];
        try {
            $categories = $this->repository->searchAfterOffset(
                $searchFilters,
                $order,
                $queryParameters['limit'],
                $offset
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name' => 'pim_api_category_list',
            'item_route_name' => 'pim_api_category_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count($searchFilters) : null;

        $paginatedCategories = $this->paginator->paginate(
            $this->normalizer->normalize(
                $categories,
                'external_api',
                ['with_position' => $request->query->getBoolean('with_position')]
            ),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedCategories);
    }

    /**
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_category_edit")
     */
    public function createAction(Request $request)
    {
        $data = $this->getDecodedContent($request->getContent());

        try {
            $command = CreateCategoryCommand::fromArray($data);
            $this->messageBus->dispatch($command);
        }
        catch (\InvalidArgumentException|InvalidPropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . 'post_categories',
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
        catch (ViolationsException $exception) {
            throw new ViolationHttpException($exception->violations());
        }

        return $this->buildResponseFromCode($data['code'], Response::HTTP_CREATED);
    }

    /**
     * @throws HttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_category_edit")
     */
    public function partialUpdateListAction(Request $request)
    {
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

        return $response;
    }

    /**
     * @param string $code
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_category_edit")
     */
    public function partialUpdateAction(Request $request, $code)
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $category = $this->repository->findOneByIdentifier($code);

        if (null === $category) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $category = $this->factory->create();
        }

        $this->updateCategory($category, $data, 'patch_categories__code_');
        $this->validateCategory($category);

        try {
            $this->saver->save($category);
        } catch (\UnexpectedValueException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($category, $status);

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
     * Update a category. It throws an error 422 if a problem occurred during the update.
     *
     * @param CategoryInterface $category category to update
     * @param array             $data     data of the request already decoded
     * @param string            $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateCategory(CategoryInterface $category, array $data, $anchor)
    {
        try {
            $this->updater->update($category, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(Documentation::URL.$anchor, sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()), $exception);
        }
    }

    /**
     * Validate a category. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @throws ViolationHttpException
     */
    protected function validateCategory(CategoryInterface $category)
    {
        $violations = $this->validator->validate($category);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param string $status
     *
     * @return Response
     */
    protected function getResponse(CategoryInterface $category, $status)
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_category_get',
            ['code' => $category->getCode()],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
    }
    /**
     * Get a response with a location header to the created or updated resource.
     */
    protected function buildResponseFromCode(string $code, string $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_category_get',
            ['code' => $code],
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
    protected function validateCodeConsistency($code, array $data)
    {
        if (isset($data['code']) && $code !== $data['code']) {
            throw new UnprocessableEntityHttpException(sprintf('The code "%s" provided in the request body must match the code "%s" provided in the url.', $data['code'], $code));
        }
    }
}

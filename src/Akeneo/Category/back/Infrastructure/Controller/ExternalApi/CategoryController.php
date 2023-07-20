<?php

namespace Akeneo\Category\Infrastructure\Controller\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use OpenApi\Attributes as OA;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryController
{
    protected SecurityFacade $securityFacade;

    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var ValidatorInterface */
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

    public function __construct(
        SecurityFacade $securityFacade,
        ApiResourceRepositoryInterface $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        StreamResourceResponse $partialUpdateStreamResource,
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->router = $router;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @return Response
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function createAction(Request $request)
    {
        $this->checkAclRights();
        $data = $this->getDecodedContent($request->getContent());

        $category = $this->factory->create();
        $this->updateCategory($category, $data, 'post_categories');
        $this->validateCategory($category);

        $this->saver->save($category);

        $response = $this->getResponse($category, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @return Response
     *
     * @throws HttpException
     */
    #[OA\Patch(
        path: "/api/rest/v1/categories",
        operationId: "patch_categories",
        description: "This endpoint allows you to update several categories at once.",
        summary: "Update/create several categories.",
        security: [
            ['bearerToken' => []],
        ],
        requestBody: new OA\RequestBody(
            description: "Contains several lines, each line is a category in JSON standard format",
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/Category_partial_update_list_request_body"
            )
        ),
        tags: ["Category"],
        responses: [
            new OA\Response(
                response: '200',
                description: 'OK',
                content: new OA\JsonContent(
                    ref: "#/components/schemas/Category_partial_update_list_response_body"
                ),
                x: [
                    'details' => 'Returns a plain text response whose lines are JSON containing the status of each update or creation.',
                    'no-entity' => true,
                ],
            ),
            new OA\Response(
                ref: '#/components/responses/401',
                response: '401'
            ),
            new OA\Response(
                ref: '#/components/responses/403',
                response: '403'
            ),
            new OA\Response(
                ref: '#/components/responses/413',
                response: '413'
            ),
            new OA\Response(
                ref: '#/components/responses/415',
                response: '415'
            ),
        ]
    )]
    public function partialUpdateListAction(Request $request)
    {
        $this->checkAclRights();
        $resource = $request->getContent(true);
        $response = $this->partialUpdateStreamResource->streamResponse($resource);

        return $response;
    }

    /**
     * @param string $code
     *
     * @return Response
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function partialUpdateAction(Request $request, $code)
    {
        $this->checkAclRights();
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
     * @return array
     *
     * @throws BadRequestHttpException
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
     * @param array $data data of the request already decoded
     * @param string $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateCategory(CategoryInterface $category, array $data, $anchor)
    {
        $this->checkAclRights();
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
            Router::ABSOLUTE_URL,
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
     * @param array $data body of the request already decoded
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency($code, array $data)
    {
        if (isset($data['code']) && $code !== $data['code']) {
            throw new UnprocessableEntityHttpException(sprintf('The code "%s" provided in the request body must match the code "%s" provided in the url.', $data['code'], $code));
        }
    }

    private function checkAclRights()
    {
        if ($this->securityFacade->isGranted('pim_api_category_edit') === false) {
            throw new AccessDeniedException();
        }
    }
}

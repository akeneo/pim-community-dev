<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Version;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\CategoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
    /** @var CategoryRepositoryInterface */
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

    /** @var string */
    protected $urlDocumentation;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param NormalizerInterface         $normalizer
     * @param SimpleFactoryInterface      $factory
     * @param ObjectUpdaterInterface      $updater
     * @param ValidatorInterface          $validator
     * @param SaverInterface              $saver
     * @param RouterInterface             $router
     * @param string                      $urlDocumentation
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        $urlDocumentation
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->router = $router;
        $this->urlDocumentation = sprintf($urlDocumentation, substr(Version::VERSION, 0, 3));
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
        $category = $this->repository->findOneByIdentifier($code);
        if (null === $category) {
            throw new NotFoundHttpException(sprintf('Category "%s" does not exist.', $code));
        }

        $categoryStandard = $this->normalizer->normalize($category, 'standard');

        return new JsonResponse($categoryStandard);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        //@TODO limit will be set in configuration in an other PR
        $limit = $request->query->get('limit', 10);
        $page = $request->query->get('page', 1);

        //@TODO add parameterValidator to validate limit and page

        $offset = $limit * ($page - 1);

        $categories = $this->repository->findBy([], ['root' => 'ASC', 'left' => 'ASC'], $limit, $offset);

        $categoriesStandard = $this->normalizer->normalize($categories, 'external_api');

        //@TODO use paginate method before return results

        return new JsonResponse($categoriesStandard);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $data = $this->getDecodedContent($request->getContent());

        $category = $this->factory->create();
        $this->updateCategory($category, $data);
        $this->validateCategory($category);

        $this->saver->save($category);

        $response = $this->getCreateResponse($category);

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

        $this->updateCategory($category, $data);
        $this->validateCategory($category, $data);

        $this->saver->save($category);

        $response = $isCreation ? $this->getCreateResponse($category) : $this->getUpdateResponse($category);

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
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function updateCategory(CategoryInterface $category, array $data)
    {
        try {
            $this->updater->update($category, $data);
        } catch (UnknownPropertyException $exception) {
            throw new DocumentedHttpException(
                $this->urlDocumentation,
                sprintf(
                    'Property "%s" does not exist. Check the standard format documentation.',
                    $exception->getPropertyName()
                ),
                $exception
            );
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                $this->urlDocumentation,
                sprintf('%s Check the standard format documentation.', $exception->getMessage()),
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
    protected function validateCategory(CategoryInterface $category)
    {
        $violations = $this->validator->validate($category);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with HTTP code 201 when an object is created.
     *
     * @param CategoryInterface $category
     *
     * @return Response
     */
    protected function getCreateResponse(CategoryInterface $category)
    {
        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate('pim_api_rest_category_get', ['code' => $category->getCode()], true);
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Get a response with HTTP code 204 when an object is updated.
     *
     * @param CategoryInterface $category
     *
     * @return Response
     */
    protected function getUpdateResponse(CategoryInterface $category)
    {
        $response = new Response(null, Response::HTTP_NO_CONTENT);
        $route = $this->router->generate('pim_api_rest_category_get', ['code' => $category->getCode()], true);
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

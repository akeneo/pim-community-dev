<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Api\Exception\BadPropertyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryController
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param NormalizerInterface         $normalizer
     * @param SimpleFactoryInterface      $factory
     * @param ObjectUpdaterInterface      $updater
     * @param ValidatorInterface          $validator
     * @param SaverInterface              $saver
     * @param RouterInterface             $router
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->factory = $factory;
        $this->router = $router;
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
     * @throws BadRequestHttpException
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            throw new BadRequestHttpException('JSON is not valid.');
        }

        $category = $this->factory->create();

        try {
            $this->updater->update($category, $data);
        } catch (NoSuchPropertyException $e) {
            throw new BadPropertyException($e->getMessage(), $e);
        }

        return $this->validateCategory($category, Response::HTTP_CREATED);
    }

    /**
     * @param CategoryInterface $category
     * @param int               $httpCode
     *
     * @return JsonResponse
     */
    protected function validateCategory(CategoryInterface $category, $httpCode)
    {
        $violations = $this->validator->validate($category);
        if (0 === $violations->count()) {
            $this->saver->save($category);

            $response = new JsonResponse(null, $httpCode);
            $route = $this->router->generate('pim_api_rest_category_get', ['code' => $category->getCode()], true);
            $response->headers->set('Location', $route);

            return $response;
        }

        // tmp, to change
        $errors = [
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Validation failed',
            'errors'  => $this->normalizer->normalize($violations, 'external_api')
        ];

        return new JsonResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Version;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /** @var string */
    protected $urlDocumentation;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param NormalizerInterface          $normalizer
     * @param SimpleFactoryInterface       $factory
     * @param ObjectUpdaterInterface       $updater
     * @param ValidatorInterface           $validator
     * @param SaverInterface               $saver
     * @param RouterInterface              $router
     * @param string                       $urlDocumentation
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
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
     *
     * @AclAncestor("pim_api_attribute_list")
     */
    public function getAction(Request $request, $code)
    {
        $attribute = $this->repository->findOneByIdentifier($code);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist.', $code));
        }

        $attributeStandard = $this->normalizer->normalize($attribute, 'standard');

        return new JsonResponse($attributeStandard);
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
        //@TODO limit will be set in configuration in an other PR
        $limit = $request->query->get('limit', 10);
        $page = $request->query->get('page', 1);

        //@TODO add parameterValidator to validate limit and page

        $offset = $limit * ($page - 1);

        $attributes = $this->repository->findBy([], [], $limit, $offset);

        $attributesStandard = $this->normalizer->normalize($attributes, 'external_api');

        //@TODO use paginate method before return results

        return new JsonResponse($attributesStandard);
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
        $this->updateAttribute($attribute, $data);

        $violations = $this->validator->validate($attribute);

        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($attribute);

        $response = $this->getCreateResponse($attribute);

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
     *
     * @throws DocumentedHttpException
     */
    protected function updateAttribute(AttributeInterface $attribute, array $data)
    {
        try {
            $this->updater->update($attribute, $data);
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
     * Get a response with HTTP code 201 when an object is created.
     *
     * @param AttributeInterface $attribute
     *
     * @return Response
     */
    protected function getCreateResponse(AttributeInterface $attribute)
    {
        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate('pim_api_rest_attribute_get', ['code' => $attribute->getCode()], true);
        $response->headers->set('Location', $route);

        return $response;
    }
}

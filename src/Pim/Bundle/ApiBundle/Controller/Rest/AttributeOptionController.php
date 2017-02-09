<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Exception\ObjectUpdaterException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Version;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
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
class AttributeOptionController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
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

    /** @var array */
    protected $supportedAttributeTypes;

    /** @var string */
    protected $urlDocumentation;

    /**
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionsRepository
     * @param NormalizerInterface                $normalizer
     * @param SimpleFactoryInterface             $factory
     * @param ObjectUpdaterInterface             $updater
     * @param ValidatorInterface                 $validator
     * @param SaverInterface                     $saver
     * @param RouterInterface                    $router
     * @param array                              $supportedAttributeTypes
     * @param string                             $urlDocumentation
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionsRepository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        $supportedAttributeTypes,
        $urlDocumentation
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionsRepository = $attributeOptionsRepository;
        $this->normalizer = $normalizer;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->router = $router;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->urlDocumentation = sprintf($urlDocumentation, substr(Version::VERSION, 0, 3));
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     * @param string  $optionCode
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $attributeCode, $optionCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $this->isAttributeSupportingOptions($attribute);

        $attributeOption = $this->attributeOptionsRepository->findOneByIdentifier($attributeCode . '.' . $optionCode);
        if (null === $attributeOption) {
            throw new NotFoundHttpException(
                sprintf(
                    'Attribute option "%s" does not exist or is not an option of the attribute "%s".',
                    $optionCode,
                    $attributeCode
                )
            );
        }

        $attributeOptionStandard = $this->normalizer->normalize($attributeOption, 'standard');

        return new JsonResponse($attributeOptionStandard);
    }

    /**
     * @param Request $request
     * @param string  $attributeCode
     *
     * @return Response
     */
    public function createAction(Request $request, $attributeCode)
    {
        $data = $this->getDecodedContent($request->getContent());

        $attribute = $this->getAttribute($attributeCode);

        $this->isAttributeSupportingOptions($attribute);

        if (isset($data['attribute']) && $data['attribute'] !== $attributeCode) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'Attribute code "%s" in the request body must match "%s" in the URI.',
                    $data['attribute'],
                    $attributeCode
                )
            );
        }

        $attributeOption = $this->factory->create();
        $this->updateAttributeOption($attributeOption, $data);

        $violations = $this->validator->validate($attributeOption);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($attributeOption);

        $response = $this->getCreateResponse($attribute, $attributeOption);

        return $response;
    }

    /**
     * Return an attribute. Throw an exception if attribute doesn't exist.
     *
     * @param string $attributeCode
     *
     * @throws NotFoundHttpException
     *
     * @return mixed
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
    protected function isAttributeSupportingOptions($attribute)
    {
        $attributeType = $attribute->getAttributeType();
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
     */
    protected function updateAttributeOption(AttributeOptionInterface $attributeOption, $data)
    {
        try {
            $this->updater->update($attributeOption, $data);
        } catch (UnknownPropertyException $exception) {
            throw new DocumentedHttpException(
                $this->urlDocumentation,
                sprintf(
                    'Property "%s" does not exist. Check the standard format documentation.',
                    $exception->getPropertyName()
                ),
                $exception
            );
        } catch (ObjectUpdaterException $exception) {
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
     * @param AttributeInterface       $attribute
     * @param AttributeOptionInterface $attributeOption
     *
     * @return Response
     */
    protected function getCreateResponse(AttributeInterface $attribute, AttributeOptionInterface $attributeOption)
    {
        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate(
            'pim_api_rest_attribute_option_get',
            [
                'attributeCode' => $attribute->getCode(),
                'optionCode'    => $attributeOption->getCode(),
            ],
            true
        );
        $response->headers->set('Location', $route);

        return $response;
    }
}

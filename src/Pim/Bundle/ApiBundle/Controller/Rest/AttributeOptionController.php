<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /** @var array */
    protected $supportedAttributeTypes;

    /**
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionsRepository
     * @param NormalizerInterface                $normalizer
     * @param array                              $supportedAttributeTypes
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionsRepository,
        NormalizerInterface $normalizer,
        $supportedAttributeTypes
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionsRepository = $attributeOptionsRepository;
        $this->normalizer = $normalizer;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
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
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist.', $attributeCode));
        }

        $attributeType = $attribute->getAttributeType();
        if (!in_array($attributeType, $this->supportedAttributeTypes)) {
            throw new NotFoundHttpException(
                sprintf(
                    'Attribute "%s" does not support options. Only attributes of type "%s" support options.',
                    $attributeCode,
                    implode('", "', $this->supportedAttributeTypes)
                )
            );
        }

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
}

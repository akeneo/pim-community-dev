<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize the value which comes as a string due to json_decode, we convert it to format expected by the updater
 *
 * TODO : the field in action could be named data, it perhaps makes more sense
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductSetValueActionValueNormalizer implements NormalizerInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * @param AttributeRepository $repository
     */
    public function __construct(AttributeRepository $repository)
    {
        $this->attributeRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        if (!isset($context['attribute_code'])) {
            throw new \InvalidArgumentException("An attribute code must be passed as context to normalize action value");
        }

        $attributeCode = $context['attribute_code'];
        $attribute = $this->attributeRepository->findOneBy(['code' => $attributeCode]);
        if (!$attribute) {
            throw new \InvalidArgumentException(sprintf('The attribute "%s" is not known', $attributeCode));
        }

        $attributeType = $attribute->getAttributeType();
        $value = $this->convert($data, $attributeType, $attributeCode);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_array($data) && $format === 'array_updater';
    }

    /**
     * @param mixed  $data
     * @param string $attributeType
     * @param string $attributeCode
     *
     * @return mixed
     */
    protected function convert($data, $attributeType, $attributeCode)
    {
        if ('pim_catalog_number' === $attributeType) {
            $value = (int) $data;

        } elseif ('pim_catalog_boolean' === $attributeType) {
            $value = (bool) $data;

        } elseif ('pim_catalog_price_collection' === $attributeType) {
            $value = [];
            foreach ($data as $price) {
                $tokens = explode(' ', $price);
                $value[] = ['data' => $tokens[0], 'currency' => $tokens[1]];
            }

        } elseif ('pim_catalog_metric' === $attributeType) {
            $tokens = explode(' ', $data);
            $value = ['data' => (float) $tokens[0], 'unit' => $tokens[1]];

        } elseif ('pim_catalog_simpleselect' === $attributeType) {
            $value = ['code' => $data, 'attribute' => $attributeCode];

        } elseif ('pim_catalog_multiselect' === $attributeType) {
            $value = [];
            foreach ($data as $option) {
                $value[] = ['code' => $option, 'attribute' => $attributeCode];
            }

        } elseif (in_array($attributeType, ['pim_catalog_image', 'pim_catalog_file'])) {
            $tokens = explode(' ', $data);
            $value = ['filePath' => realpath($tokens[0]), 'originalFilename' => $tokens[1]];

        } else {
            $value = (string) $data;
        }

        return $value;
    }
}

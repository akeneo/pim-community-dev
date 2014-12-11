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
 * Normalize set value rule actions data, comes as string due to json_decode
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
        $value = $data;
        // TODO : cover the cases will be done in feature branch
        switch ($attributeType) {
            case 'pim_catalog_text':
            case 'pim_catalog_textarea':
            case 'pim_catalog_date':
            case 'pim_catalog_identifier':
                $value = (string) $data;
                break;
            case 'pim_catalog_number':
                $value = (int) $data;
                break;
            /*
            case 'pim_catalog_metric':
            case 'pim_catalog_multiselect':
            */
            case 'pim_catalog_price_collection':
                $value = [];
                foreach ($data as $price) {
                    $tokens = explode(' ', $price);
                    $value[] = ['data' => $tokens[0], 'currency' => $tokens[1]];
                }
                break;
            case 'pim_catalog_simpleselect':
                $value = ['code' => $data, 'attribute' => $attribute->getCode()];
                break;
            case 'pim_catalog_boolean':
                $value = (bool) $data;
                break;
            case 'pim_catalog_image':
            case 'pim_catalog_file':
                $values = explode(',', $data);
                $value = ['originalFilename' => $values[0], 'filePath' => $values[1]];
            break;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_array($data) && $format === 'array_updater';
    }
}

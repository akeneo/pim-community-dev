<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform a group entity into an array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = array('json', 'xml');

    /** @var TranslationNormalizer $transNormalizer */
    protected $transNormalizer;

    /**
     * Constructor
     *
     * @param TranslationNormalizer $transNormalizer
     */
    public function __construct(TranslationNormalizer $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $results = [
            'code' => $object->getCode(),
            'type' => $object->getType()->getCode()
        ];

        $axisAttributes = $this->normalizeAxisAttributes($object);
        if (!empty($axisAttributes)) {
            $results += ['axis' => $axisAttributes];
        }

        $results += $this->transNormalizer->normalize($object, $format, $context);

        if (isset($context['with_variant_group_values']) && true === $context['with_variant_group_values']) {
            $variantGroupValues = $this->normalizeVariantGroupValues($object, $format, $context);
            if (!empty($variantGroupValues)) {
                $results += $variantGroupValues;
            }
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param GroupInterface $group
     *
     * @return array
     */
    protected function normalizeAxisAttributes(GroupInterface $group)
    {
        $attributes = array();
        foreach ($group->getAxisAttributes() as $attribute) {
            $attributes[] = $attribute->getCode();
        }
        sort($attributes);

        return $attributes;
    }

    /**
     * Normalize the variant group values
     *
     * @param GroupInterface $group
     * @param string         $format
     * @param array          $context
     *
     * @return array
     */
    protected function normalizeVariantGroupValues(GroupInterface $group, $format, array $context)
    {
        $valuesData = [];
        if ($group->getType()->isVariant() && null !== $group->getProductTemplate()) {
            $template = $group->getProductTemplate();
            $valuesData = $template->getValuesData();
        }

        return $valuesData;
    }
}

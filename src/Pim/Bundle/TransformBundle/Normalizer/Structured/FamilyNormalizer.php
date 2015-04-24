<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var TranslationNormalizer
     */
    protected $transNormalizer;

    /**
     * @var array
     */
    protected $attributeFilters = [];

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
    public function setFilters(array $filters)
    {
        $this->attributeFilters = $filters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'code'             => $object->getCode(),
            'attributes'       => $this->normalizeAttributes($object, $context),
            'attributeAsLabel' => ($object->getAttributeAsLabel()) ? $object->getAttributeAsLabel()->getCode() : '',
            'requirements'     => $this->normalizeRequirements($object),
        ) + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function normalizeAttributes(FamilyInterface $family, array $context = [])
    {
        $attributes = $family->getAttributes();

        foreach ($this->attributeFilters as $filter) {
            $attributes = $filter->filter($attributes, $context);
        }

        $normalizedAttributes = array();
        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = $attribute->getCode();
        }

        return $normalizedAttributes;
    }

    /**
     * Normalize the requirements
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function normalizeRequirements(FamilyInterface $family)
    {
        $required = array();
        foreach ($family->getAttributeRequirements() as $requirement) {
            $channelCode = $requirement->getChannel()->getCode();
            if (!isset($required[$channelCode])) {
                $required[$channelCode] = array();
            }
            if ($requirement->isRequired()) {
                $required[$channelCode][] = $requirement->getAttribute()->getCode();
            }
        }

        return $required;
    }
}

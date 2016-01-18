<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
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
    /** @var string[] */
    protected $supportedFormats = ['json', 'xml'];

    /** @var TranslationNormalizer */
    protected $transNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * Constructor
     *
     * @param TranslationNormalizer          $transNormalizer
     * @param CollectionFilterInterface|null $collectionFilter
     */
    public function __construct(
        TranslationNormalizer $transNormalizer,
        CollectionFilterInterface $collectionFilter = null
    ) {
        $this->transNormalizer  = $transNormalizer;
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'code'             => $object->getCode(),
            'attributes'       => $this->normalizeAttributes($object),
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
    protected function normalizeAttributes(FamilyInterface $family)
    {
        $filteredAttributes = $this->collectionFilter ?
            $this->collectionFilter->filterCollection(
                $family->getAttributes(),
                'pim.internal_api.attribute.view'
            ) :
            $family->getAttributes();

        $normalizedAttributes = array();
        foreach ($filteredAttributes as $attribute) {
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

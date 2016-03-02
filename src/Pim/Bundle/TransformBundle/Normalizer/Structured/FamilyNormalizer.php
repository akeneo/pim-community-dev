<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
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
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedRequirements = $this->normalizeRequirements($object);
        $transNormalized = $this->transNormalizer->normalize($object, $format, $context);

        $defaults = ['code' => $object->getCode()];

        $normalizedData = [
            'attributes'         => $this->normalizeAttributes($object),
            'attribute_as_label' => ($object->getAttributeAsLabel()) ? $object->getAttributeAsLabel()->getCode() : '',
        ];

        return array_merge($defaults, $transNormalized, $normalizedData, $normalizedRequirements);
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

        $normalizedAttributes = [];
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
        $required = [];
        foreach ($family->getAttributeRequirements() as $requirement) {
            $channelCode = $requirement->getChannel()->getCode();
            if (!isset($required['requirements-' . $channelCode])) {
                $required['requirements-' . $channelCode] = [];
            }
            if ($requirement->isRequired()) {
                $required['requirements-' . $channelCode][] = $requirement->getAttribute()->getCode();
            }
        }

        return $required;
    }
}

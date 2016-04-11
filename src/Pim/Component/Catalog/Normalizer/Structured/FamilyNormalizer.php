<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeRequirementRepositoryInterface */
    protected $requirementsRepository;

    /**
     * Constructor
     *
     * @param TranslationNormalizer                   $transNormalizer
     * @param CollectionFilterInterface               $collectionFilter
     * @param AttributeRepositoryInterface            $attributeRepository
     * @param AttributeRequirementRepositoryInterface $requirementsRepository
     */
    public function __construct(
        TranslationNormalizer $transNormalizer,
        CollectionFilterInterface $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeRequirementRepositoryInterface $requirementsRepository
    ) {
        $this->transNormalizer = $transNormalizer;
        $this->collectionFilter = $collectionFilter;
        $this->attributeRepository = $attributeRepository;
        $this->requirementsRepository = $requirementsRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param FamilyInterface $object
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
        $attributes = $this->collectionFilter->filterCollection(
            $this->attributeRepository->findAttributesByFamily($family),
            'pim.internal_api.attribute.view'
        );

        $normalizedAttributes = [];
        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = $attribute->getCode();
        }

        sort($normalizedAttributes);

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
        $requirements = $this->requirementsRepository->findRequiredAttributesCodesByFamily($family);
        $required = [];

        usort($requirements, function ($left, $right) {
            if ($left['channel'] !== $right['channel']) {
                return $left['channel'] < $right['channel'] ? -1 : 1;
            }

            if ($left['attribute'] !== $right['attribute']) {
                return $left['attribute'] < $right['attribute'] ? -1 : 1;
            }

            return 0;
        });

        foreach ($requirements as $requirement) {
            $required[sprintf('requirements-%s', $requirement['channel'])][] = $requirement['attribute'];
        }

        return $required;
    }
}

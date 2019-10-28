<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeRequirementRepositoryInterface */
    protected $attributeRequirementRepo;

    /**
     * @param NormalizerInterface                     $translationNormalizer
     * @param CollectionFilterInterface               $collectionFilter
     * @param AttributeRepositoryInterface            $attributeRepository
     * @param AttributeRequirementRepositoryInterface $attributeRequirementRepo
     */
    public function __construct(
        NormalizerInterface $translationNormalizer,
        CollectionFilterInterface $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeRequirementRepositoryInterface $attributeRequirementRepo
    ) {
        $this->translationNormalizer = $translationNormalizer;
        $this->attributeRequirementRepo = $attributeRequirementRepo;
        $this->collectionFilter = $collectionFilter;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($family, $format = null, array $context = [])
    {
        return [
            'code'                   => $family->getCode(),
            'attributes'             => $this->normalizeAttributes($family),
            'attribute_as_label'     => null !== $family->getAttributeAsLabel()
                ? $family->getAttributeAsLabel()->getCode() : null,
            'attribute_as_image'     => null !== $family->getAttributeAsImage()
                ? $family->getAttributeAsImage()->getCode() : null,
            'attribute_requirements' => $this->normalizeRequirements($family),
            'labels'                 => $this->translationNormalizer->normalize($family, 'standard', $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
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
        $requirements = $this->attributeRequirementRepo->findRequiredAttributesCodesByFamily($family);
        $required = [];

        foreach ($requirements as $requirement) {
            $required[$requirement['channel']][] = $requirement['attribute'];
        }

        return $required;
    }
}

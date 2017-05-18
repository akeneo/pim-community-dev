<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class FamilyNormalizer
 *
 * @author Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $familyNormalizer;

    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /**
     * @param NormalizerInterface          $familyNormalizer
     * @param NormalizerInterface          $translationNormalizer
     * @param CollectionFilterInterface    $collectionFilter
     * @param AttributeRepositoryInterface $attributeRepository
     * @param VersionManager               $versionManager
     * @param NormalizerInterface          $versionNormalizer
     */
    public function __construct(
        NormalizerInterface $familyNormalizer,
        NormalizerInterface $translationNormalizer,
        CollectionFilterInterface $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer
    ) {
        $this->familyNormalizer = $familyNormalizer;
        $this->translationNormalizer = $translationNormalizer;
        $this->collectionFilter = $collectionFilter;
        $this->attributeRepository = $attributeRepository;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($family, $format = null, array $context = array())
    {
        $applyFilters = array_key_exists('apply_filters', $context)
            && true === $context['apply_filters'];

        $normalizedFamily = $this->familyNormalizer->normalize(
            $family,
            'standard',
            $context
        );

        $normalizedFamily['attributes'] = $this->normalizeAttributes($family, $applyFilters);

        $normalizedFamily['attribute_requirements'] = $this->normalizeRequirements(
            $normalizedFamily['attribute_requirements'],
            $applyFilters
        );

        $firstVersion = $this->versionManager->getOldestLogEntry($family);
        $lastVersion = $this->versionManager->getNewestLogEntry($family);

        $created = null === $firstVersion ? null :
            $this->versionNormalizer->normalize($firstVersion, 'internal_api');
        $updated = null === $lastVersion ? null :
            $this->versionNormalizer->normalize($lastVersion, 'internal_api');

        $normalizedFamily['meta'] = [
            'id'      => $family->getId(),
            'form'    => 'pim-family-edit-form',
            'created' => $created,
            'updated' => $updated,
        ];

        return $normalizedFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($family, $format = null)
    {
        return $family instanceof FamilyInterface &&
            in_array($format, $this->supportedFormats);
    }

    /**
     * Fetches attributes by code and normalizes them
     *
     * @param FamilyInterface $family
     * @param boolean         $applyFilters
     *
     * @return array
     */
    protected function normalizeAttributes(FamilyInterface $family, $applyFilters)
    {
        $attributes = $this->attributeRepository->findAttributesByFamily($family);

        if ($applyFilters) {
            $attributes = $this->collectionFilter->filterCollection(
                $attributes,
                'pim.internal_api.attribute.view'
            );
        }

        $normalizedAttributes = [];
        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = [
                'code' => $attribute->getCode(),
                'type' => $attribute->getType(),
                'group_code' => $attribute->getGroup()->getCode(),
                'labels' => $this->translationNormalizer->normalize($attribute, 'standard', []),
                'sort_order' => $attribute->getSortOrder(),
            ];
        }

        return $normalizedAttributes;
    }

    /**
     * Normalize the requirements
     *
     * It filters the requirements to the viewable ones
     *
     * @param array $requirements
     * @param bool  $applyFilters
     *
     * @return array
     */
    protected function normalizeRequirements($requirements, $applyFilters)
    {
        $result = [];

        foreach ($requirements as $channel => $attributeCodes) {
            $attributes = $this->attributeRepository->findBy(['code' => $attributeCodes]);

            if ($applyFilters) {
                $attributes = $this->collectionFilter->filterCollection(
                    $attributes,
                    'pim.internal_api.attribute.view'
                );
            }

            $result[$channel] = array_map(function ($attribute) {
                return $attribute->getCode();
            }, $attributes);
        }

        return $result;
    }
}

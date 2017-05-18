<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Form\NoCompatibleFormProviderFoundException;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FieldProviderInterface */
    protected $fieldProvider;

    /** @var EmptyValueProviderInterface */
    protected $emptyValueProvider;

    /** @var FilterProviderInterface */
    protected $filterProvider;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var FormProviderInterface */
    private $formProvider;

    /**
     * @param NormalizerInterface         $normalizer
     * @param FieldProviderInterface      $fieldProvider
     * @param EmptyValueProviderInterface $emptyValueProvider
     * @param FilterProviderInterface     $filterProvider
     * @param VersionManager              $versionManager
     * @param NormalizerInterface         $versionNormalizer
     * @param FormProviderInterface       $formProvider
     */
    public function __construct(
        NormalizerInterface $normalizer,
        FieldProviderInterface $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface $filterProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        FormProviderInterface $formProvider
    ) {
        $this->normalizer = $normalizer;
        $this->fieldProvider = $fieldProvider;
        $this->emptyValueProvider = $emptyValueProvider;
        $this->filterProvider = $filterProvider;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
        $this->formProvider = $formProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $dateMin = (null === $attribute->getDateMin()) ? '' : $attribute->getDateMin()->format(\DateTime::ISO8601);
        $dateMax = (null === $attribute->getDateMax()) ? '' : $attribute->getDateMax()->format(\DateTime::ISO8601);
        $groupCode = (null === $attribute->getGroup()) ? null : $attribute->getGroup()->getCode();

        $normalizedAttribute = $this->normalizer->normalize($attribute, 'standard', $context) + [
            'empty_value'        => $this->emptyValueProvider->getEmptyValue($attribute),
            'field_type'         => $this->fieldProvider->getField($attribute),
            'filter_types'       => $this->filterProvider->getFilters($attribute),
            'is_locale_specific' => $attribute->isLocaleSpecific(),
            'date_min'           => $dateMin,
            'date_max'           => $dateMax,
            'group_code'         => $groupCode,
        ];

        $firstVersion = $this->versionManager->getOldestLogEntry($attribute);
        $lastVersion = $this->versionManager->getNewestLogEntry($attribute);

        $firstVersion = null !== $firstVersion ?
            $this->versionNormalizer->normalize($firstVersion, 'internal_api') :
            null;
        $lastVersion = null !== $lastVersion ?
            $this->versionNormalizer->normalize($lastVersion, 'internal_api') :
            null;

        $normalizedAttribute['meta'] = [
            'id'      => $attribute->getId(),
            'created' => $firstVersion,
            'updated' => $lastVersion,
            'form'    => 'pim-attribute-edit-form',
        ];

        try {
            $additionalForm = $this->formProvider->getForm($attribute);
        } catch (NoCompatibleFormProviderFoundException $e) {
            $additionalForm = null;
        }

        $normalizedAttribute['meta']['additional_form'] = $additionalForm;

        // This normalizer is used in the PEF attributes loading and in the add_attributes widget. The attributes
        // loading does not need complete group normalization. This has to be cleaned.
        $normalizedAttribute['group'] = null;

        if (isset($context['include_group']) && $context['include_group'] && null !== $attribute->getGroup()) {
            $normalizedAttribute['group'] = $this->normalizer->normalize($attribute->getGroup(), 'standard', $context);
        }

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }
}

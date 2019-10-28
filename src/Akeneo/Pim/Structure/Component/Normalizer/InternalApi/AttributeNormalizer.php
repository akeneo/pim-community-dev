<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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

    /** @var LocalizerInterface */
    protected $numberLocalizer;

    /**
     * @param NormalizerInterface               $normalizer
     * @param FieldProviderInterface            $fieldProvider
     * @param EmptyValueProviderInterface       $emptyValueProvider
     * @param FilterProviderInterface           $filterProvider
     * @param LocalizerInterface                $numberLocalizer
     */
    public function __construct(
        NormalizerInterface $normalizer,
        FieldProviderInterface $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface $filterProvider,
        LocalizerInterface $numberLocalizer
    ) {
        $this->normalizer = $normalizer;
        $this->fieldProvider = $fieldProvider;
        $this->emptyValueProvider = $emptyValueProvider;
        $this->filterProvider = $filterProvider;
        $this->numberLocalizer = $numberLocalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $dateMin = null === $attribute->getDateMin() ? null : $attribute->getDateMin()->format('Y-m-d');
        $dateMax = null === $attribute->getDateMax() ? null : $attribute->getDateMax()->format('Y-m-d');

        $normalizedAttribute = array_merge(
            $this->normalizer->normalize($attribute, 'standard', $context),
            [
                'empty_value'        => $this->emptyValueProvider->getEmptyValue($attribute),
                'field_type'         => $this->fieldProvider->getField($attribute),
                'filter_types'       => $this->filterProvider->getFilters($attribute),
                'is_locale_specific' => $attribute->isLocaleSpecific(),
                'date_min'           => $dateMin,
                'date_max'           => $dateMax,
            ]
        );

        if (isset($context['locale'])) {
            $normalizedAttribute['number_min'] = $this->numberLocalizer->localize(
                $normalizedAttribute['number_min'],
                ['locale' => $context['locale']]
            );

            $normalizedAttribute['number_max'] = $this->numberLocalizer->localize(
                $normalizedAttribute['number_max'],
                ['locale' => $context['locale']]
            );
        }

        $normalizedAttribute['meta']['id'] = $attribute->getId();

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

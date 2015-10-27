<?php

namespace Pim\Component\Localization\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product values with a localized format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var NormalizerInterface */
    protected $valuesNormalizer;

    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /**
     * @param NormalizerInterface        $valuesNormalizer
     * @param LocalizerRegistryInterface $localizerRegistry
     */
    public function __construct(NormalizerInterface $valuesNormalizer, LocalizerRegistryInterface $localizerRegistry)
    {
        $this->valuesNormalizer  = $valuesNormalizer;
        $this->localizerRegistry = $localizerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        $result = $this->valuesNormalizer->normalize($entity, $format, $context);

        $type = $entity->getAttribute()->getAttributeType();

        $localizer = $this->localizerRegistry->getProductValueLocalizer($type);
        if (null !== $localizer) {
            $result['data'] = $localizer->localize($result['data'], $context);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }
}

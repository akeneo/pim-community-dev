<?php

namespace Pim\Component\Localization\Normalizer\Structured;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a price with a localized format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var NormalizerInterface */
    protected $priceNormalizer;

    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param NormalizerInterface $priceNormalizer
     * @param LocalizerInterface  $localizer
     */
    public function __construct(NormalizerInterface $priceNormalizer, LocalizerInterface $localizer)
    {
        $this->priceNormalizer = $priceNormalizer;
        $this->localizer       = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($price, $format = null, array $context = [])
    {
        $price = $this->priceNormalizer->normalize($price, $format, $context);

        $price['data'] = $this->localizer->localize($price['data'], $context);

        return $price;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPriceInterface && in_array($format, $this->supportedFormats);
    }
}

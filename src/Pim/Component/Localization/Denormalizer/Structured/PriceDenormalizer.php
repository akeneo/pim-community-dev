<?php

namespace Pim\Component\Localization\Denormalizer\Structured;

use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['json'];

    /** @var DenormalizerInterface */
    protected $priceDenormalizer;

    /** @var LocalizerInterface */
    protected $localizer;

    /** @var string[] */
    protected $supportedTypes;

    /**
     * @param DenormalizerInterface $priceDenormalizer
     * @param LocalizerInterface    $localizer
     * @param string[]              $supportedTypes
     */
    public function __construct(
        DenormalizerInterface $priceDenormalizer,
        LocalizerInterface $localizer,
        array $supportedTypes
    ) {
        $this->priceDenormalizer = $priceDenormalizer;
        $this->localizer         = $localizer;
        $this->supportedTypes    = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $prices = $this->priceDenormalizer->denormalize($data, $class, $format, $context);

        foreach ($prices as $price) {
            $price->setData($this->localizer->convertDefaultToLocalized($price->getData(), $context));
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
    }
}

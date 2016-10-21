<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Channel normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var NormalizerInterface */
    protected $transNormalizer;

    /**
     * @param NormalizerInterface $transNormalizer
     */
    public function __construct(NormalizerInterface $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param $object ChannelInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'code'             => $object->getCode(),
            'currencies'       => $this->normalizeCurrencies($object),
            'locales'          => $this->normalizeLocales($object),
            'category'         => $this->normalizeCategoryTree($object),
            'conversion_units' => $this->normalizeConversionUnits($object),
        ] + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns an array containing the currency values
     *
     * @param ChannelInterface $channel
     *
     * @return array
     */
    protected function normalizeCurrencies(ChannelInterface $channel)
    {
        $currencies = [];
        foreach ($channel->getCurrencies() as $currency) {
            $currencies[] = $currency->getCode();
        }

        return $currencies;
    }

    /**
     * Returns an array containing the locale values
     *
     * @param ChannelInterface $channel
     *
     * @return array
     */
    protected function normalizeLocales(ChannelInterface $channel)
    {
        $locales = [];
        foreach ($channel->getLocales() as $locale) {
            $locales[] = $locale->getCode();
        }

        return $locales;
    }

    /**
     * Returns normalized category
     *
     * @param ChannelInterface $channel
     *
     * @return string
     */
    protected function normalizeCategoryTree(ChannelInterface $channel)
    {
        $translations = $channel->getCategory()->getTranslations();
        $labels = [];

        foreach ($translations as $translation) {
            $labels[$translation->getLocale()] = $translation->getLabel();
        }

        return [
            'id'     => $channel->getCategory()->getId(),
            'code'   => $channel->getCategory()->getCode(),
            'labels' => $labels,
        ];
    }

    /**
     * Returns conversion units
     *
     * @param ChannelInterface $channel
     *
     * @return string
     */
    protected function normalizeConversionUnits(ChannelInterface $channel)
    {
        $result = [];
        foreach ($channel->getConversionUnits() as $family => $unit) {
            $result[] = sprintf('%s: %s', $family, $unit);
        }

        return implode(', ', $result);
    }
}

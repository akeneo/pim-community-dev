<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Channel normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'code'             => $object->getCode(),
            'label'            => $this->normalizeLabel($object),
            'currencies'       => $this->normalizeCurrencies($object),
            'locales'          => $this->normalizeLocales($object),
            'category'         => $this->normalizeCategoryTree($object),
            'conversion_units' => $this->normalizeConversionUnits($object),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Channel && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize label property
     *
     * @param Channel $channel
     *
     * @return string
     */
    protected function normalizeLabel(Channel $channel)
    {
        return $channel->getLabel();
    }

    /**
     * Returns an array containing the currency values
     *
     * @param Channel $channel
     *
     * @return array
     */
    protected function normalizeCurrencies(Channel $channel)
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
     * @param Channel $channel
     *
     * @return array
     */
    protected function normalizeLocales(Channel $channel)
    {
        $locales = [];
        foreach ($channel->getLocales() as $locale) {
            $locales[] = $locale->getCode();
        }

        return $locales;
    }

    /**
     * Returns category tree code
     *
     * @param Channel $channel
     *
     * @return string
     */
    protected function normalizeCategoryTree(Channel $channel)
    {
        return $channel->getCategory()->getCode();
    }

    /**
     * Returns conversion units
     *
     * @param Channel $channel
     *
     * @return array
     */
    protected function normalizeConversionUnits(Channel $channel)
    {
        $result = [];
        foreach ($channel->getConversionUnits() as $family => $unit) {
            $result[] = sprintf('%s: %s', $family, $unit);
        }

        return implode(', ', $result);
    }
}

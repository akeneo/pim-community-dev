<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface
{
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
     */
    public function normalize($channel, $format = null, array $context = [])
    {
        return [
            'code'             => $channel->getCode(),
            'currencies'       => $this->normalizeCurrencies($channel),
            'locales'          => $channel->getLocaleCodes(),
            'category_tree'    => $channel->getCategory()->getCode(),
            'conversion_units' => $channel->getConversionUnits(),
            'labels'           => $this->transNormalizer->normalize($channel, $format, $context)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelInterface && 'standard' === $format;
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
}

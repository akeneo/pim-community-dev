<?php

namespace Pim\Component\Connector\Normalizer\Flat;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Normalizer\Structured\ChannelNormalizer as BaseNormalizer;

/**
 * A normalizer to transform a channel entity into a flat array
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     *
     * @param $object ChannelInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'code'       => $object->getCode(),
            'label'      => $this->normalizeLabel($object),
            'currencies' => $this->normalizeCurrencies($object),
            'locales'    => $this->normalizeLocales($object),
            'tree'       => $this->normalizeCategoryTree($object),
            'color'      => $object->getColor(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeCurrencies(ChannelInterface $channel)
    {
        $currencies = parent::normalizeCurrencies($channel);
        asort($currencies);

        return implode(',', $currencies);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeLocales(ChannelInterface $channel)
    {
        $locales = parent::normalizeLocales($channel);
        asort($locales);

        return implode(',', $locales);
    }
}

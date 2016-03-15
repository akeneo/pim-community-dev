<?php

namespace Pim\Component\Connector\Normalizer;

use Pim\Component\Catalog\Normalizer\ChannelNormalizer as BaseNormalizer;
use Pim\Component\Catalog\Model\ChannelInterface;

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
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeCurrencies(ChannelInterface $channel)
    {
        $currencies = parent::normalizeCurrencies($channel);

        return implode(',', $currencies);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeLocales(ChannelInterface $channel)
    {
        $locales = parent::normalizeLocales($channel);

        return implode(',', $locales);
    }
}

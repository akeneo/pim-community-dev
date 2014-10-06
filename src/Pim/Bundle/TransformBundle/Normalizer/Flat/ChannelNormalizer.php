<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * A normalizer to transform a channel entity into a flat array
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer extends Structured\ChannelNormalizer
{
    /**
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeCurrencies(Channel $channel)
    {
        $currencies = parent::normalizeCurrencies($channel);

        return implode(',', $currencies);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeLocales(Channel $channel)
    {
        $locales = parent::normalizeLocales($channel);

        return implode(',', $locales);
    }
}

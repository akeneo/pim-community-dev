<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Calculator;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectItemCalculatorInterface
{
    /**
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return array
     */
    public function calculate(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    );
}

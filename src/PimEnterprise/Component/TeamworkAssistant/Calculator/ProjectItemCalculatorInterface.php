<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Calculator;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

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

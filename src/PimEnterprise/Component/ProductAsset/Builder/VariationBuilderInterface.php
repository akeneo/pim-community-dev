<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Builds variations related to an asset reference
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface VariationBuilderInterface
{
    /**
     * @param ReferenceInterface $reference
     *
     * @return VariationInterface[]
     */
    public function buildAll(ReferenceInterface $reference);

    /**
     * @param ReferenceInterface $reference
     *
     * @return VariationInterface[]
     */
    public function buildMissing(ReferenceInterface $reference);

    /**
     * @param ReferenceInterface $reference
     * @param ChannelInterface   $channel
     *
     * @throws \LogicException in case it's impossible to build the variation
     *
     * @return VariationInterface
     */
    public function buildOne(ReferenceInterface $reference, ChannelInterface $channel);
}

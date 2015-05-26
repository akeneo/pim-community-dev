<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

/**
 * Configuration interface of a channel for the product asset variations
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ChannelVariationsConfigurationInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * @param ChannelInterface $channel
     *
     * @return ChannelVariationsConfigurationInterface
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @param array $configuration
     *
     * @return ChannelVariationsConfigurationInterface
     */
    public function setConfiguration(array $configuration);
}

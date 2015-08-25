<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Factory;

use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;

/**
 * Channel Configuration Factory
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChannelConfigurationFactory
{
    /** @var string */
    protected $configurationClass;

    /**
     * @param string $configurationClass
     */
    public function __construct($configurationClass)
    {
        $this->configurationClass = $configurationClass;
    }

    /**
     * Create a new empty Tag
     *
     * @return ChannelVariationsConfigurationInterface
     */
    public function createChannelConfiguration()
    {
        return new $this->configurationClass();
    }
}

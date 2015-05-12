<?php
namespace PimEnterprise\Component\ProductAsset\Model;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

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

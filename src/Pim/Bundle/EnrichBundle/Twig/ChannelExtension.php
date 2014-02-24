<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Twig extension to get channel colors
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelExtension extends \Twig_Extension
{
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * Constructor
     *
     * @param ChannelManager $channelManager
     */
    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'channel_color' => new \Twig_Function_Method($this, 'channelColor')
        ];
    }

    /**
     * Get channel color
     *
     * @param string $code
     *
     * @return string
     */
    public function channelColor($code)
    {
        $channel = $this->channelManager->getChannelByCode($code);

        return $channel ? $channel->getColor() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_channel_extension';
    }
}

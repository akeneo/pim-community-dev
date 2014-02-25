<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\EnrichBundle\Provider\ColorsProvider;

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
     * @var ColorsProvider
     */
    protected $colorsProvider;

    /**
     * Constructor
     *
     * @param ChannelManager $channelManager
     * @param ColorsProvider $colorsProvider
     */
    public function __construct(ChannelManager $channelManager, ColorsProvider $colorsProvider)
    {
        $this->channelManager = $channelManager;
        $this->colorsProvider = $colorsProvider;
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

        return $channel ? $this->colorsProvider->getColorCode($channel->getColor()) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_channel_extension';
    }
}

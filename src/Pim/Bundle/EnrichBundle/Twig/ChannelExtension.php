<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\EnrichBundle\Provider\ColorsProvider;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Twig extension to get channel colors
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelExtension extends \Twig_Extension
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**  @var ColorsProvider */
    protected $colorsProvider;

    /**
     * Constructor
     *
     * @param ChannelRepositoryInterface $channelRepository
     * @param ColorsProvider             $colorsProvider
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, ColorsProvider $colorsProvider)
    {
        $this->channelRepository = $channelRepository;
        $this->colorsProvider    = $colorsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'channel_color'      => new \Twig_Function_Method($this, 'channelColor'),
            'channel_font_color' => new \Twig_Function_Method($this, 'channelFontColor')
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
        $channel = $this->channelRepository->findOneByIdentifier(['code' => $code]);

        return $channel ? $this->colorsProvider->getColorCode($channel->getColor()) : '';
    }

    /**
     * Get channel font color
     *
     * @param string $code
     *
     * @return string
     */
    public function channelFontColor($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);

        return $channel ? $this->colorsProvider->getFontColor($channel->getColor()) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_channel_extension';
    }
}

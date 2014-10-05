<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Locale interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocaleInterface extends ReferableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return LocaleInterface
     */
    public function setCode($code);

    /**
     * @return bool
     */
    public function isActivated();

    /**
     * @return ArrayCollection
     */
    public function getChannels();

    /**
     * @param ArrayCollection $channels
     *
     * @return LocaleInterface
     */
    public function setChannels($channels);

    /**
     * @param ChannelInterface $channel
     *
     * @return LocaleInterface
     */
    public function addChannel(ChannelInterface $channel);

    /**
     * @param ChannelInterface $channel
     *
     * @return LocaleInterface
     */
    public function removeChannel(ChannelInterface $channel);
}

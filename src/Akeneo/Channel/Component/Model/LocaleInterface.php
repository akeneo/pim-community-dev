<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @param int $id
     *
     * @return LocaleInterface
     */
    public function setId($id);

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
     * @return string|null
     */
    public function getLanguage();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return bool
     */
    public function isActivated();

    /**
     * @return ArrayCollection
     */
    public function getChannels();

    /**
     * @param ChannelInterface $channel
     *
     * @return bool
     */
    public function hasChannel(ChannelInterface $channel);

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

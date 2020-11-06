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
    public function getId(): int;

    /**
     * @param int $id
     */
    public function setId(int $id): \Akeneo\Channel\Component\Model\LocaleInterface;

    public function getCode(): string;

    /**
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Channel\Component\Model\LocaleInterface;

    /**
     * @return string|null
     */
    public function getLanguage(): ?string;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    public function isActivated(): bool;

    public function getChannels(): \Doctrine\Common\Collections\ArrayCollection;

    /**
     * @param ChannelInterface $channel
     */
    public function hasChannel(ChannelInterface $channel): bool;

    /**
     * @param ArrayCollection $channels
     */
    public function setChannels(\Doctrine\Common\Collections\ArrayCollection $channels): \Akeneo\Channel\Component\Model\LocaleInterface;

    /**
     * @param ChannelInterface $channel
     */
    public function addChannel(ChannelInterface $channel): \Akeneo\Channel\Component\Model\LocaleInterface;

    /**
     * @param ChannelInterface $channel
     */
    public function removeChannel(ChannelInterface $channel): \Akeneo\Channel\Component\Model\LocaleInterface;
}

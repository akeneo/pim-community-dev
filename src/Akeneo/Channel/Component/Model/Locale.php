<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Locale entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Locale implements LocaleInterface, VersionableInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var bool
     */
    protected $activated = false;

    /**
     * @var ArrayCollection
     */
    protected $channels;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return (null === $this->code) ? null : substr($this->code, 0, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(ChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function setChannels($channels)
    {
        $this->channels = new ArrayCollection();
        $this->activated = false;

        foreach ($channels as $channel) {
            $this->addChannel($channel);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(ChannelInterface $channel)
    {
        $this->channels[] = $channel;
        if ($this->channels->count() > 0) {
            $this->activated = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(ChannelInterface $channel)
    {
        $this->channels->removeElement($channel);
        if ($this->channels->count() === 0) {
            $this->activated = false;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return null !== $this->code ? \Locale::getDisplayName($this->code) : null;
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Locale entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @UniqueEntity("code")
 *
 * @Config(
 *     defaultValues={
 *         "entity"={"label"="Locale", "plural_label"="Locales"}
 *     }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Locale implements ReferableInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var boolean $activated
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Locale
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Locale
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Is activated
     *
     * @return boolean
     */
    public function isActivated()
    {
        return $this->channels->count() > 0;
    }

    /**
     * Get channels
     *
     * @return ArrayCollection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Set channels
     *
     * @param ArrayCollection $channels
     *
     * @return Locale
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Add channel
     *
     * @param Channel $channel
     *
     * @return Locale
     */
    public function addChannel(Channel $channel)
    {
        $this->channels[] = $channel;
        if ($this->channels->count() > 0) {
            $this->activated = true;
        }

        return $this;
    }

    /**
     * Remove channel
     *
     * @param Channel $channel
     *
     * @return Locale
     */
    public function removeChannel(Channel $channel)
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
}

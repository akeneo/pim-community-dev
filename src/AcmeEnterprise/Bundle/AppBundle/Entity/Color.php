<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmeEnterprise\Bundle\AppBundle\Entity;

use Pim\Component\ReferenceData\Model\AbstractReferenceData;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Acme Color entity (used as simple reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class Color extends AbstractReferenceData implements ReferenceDataInterface
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $hex;

    /** @var int */
    protected $red;

    /** @var int */
    protected $green;

    /** @var int */
    protected $blue;

    /** @var int */
    protected $hue;

    /** @var int */
    protected $hslSaturation;

    /** @var int */
    protected $light;

    /** @var int */
    protected $hsvSaturation;

    /** @var int */
    protected $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHex()
    {
        return $this->hex;
    }

    /**
     * @param string $hex
     */
    public function setHex($hex)
    {
        $this->hex = $hex;
    }

    /**
     * @return int
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * @param int $red
     */
    public function setRed($red)
    {
        $this->red = $red;
    }

    /**
     * @return int
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * @param int $green
     */
    public function setGreen($green)
    {
        $this->green = $green;
    }

    /**
     * @return int
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * @param int $blue
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;
    }

    /**
     * @return int
     */
    public function getHue()
    {
        return $this->hue;
    }

    /**
     * @param int $hue
     */
    public function setHue($hue)
    {
        $this->hue = $hue;
    }

    /**
     * @return int
     */
    public function getHslSaturation()
    {
        return $this->hslSaturation;
    }

    /**
     * @param int $hslSaturation
     */
    public function setHslSaturation($hslSaturation)
    {
        $this->hslSaturation = $hslSaturation;
    }

    /**
     * @return int
     */
    public function getLight()
    {
        return $this->light;
    }

    /**
     * @param int $light
     */
    public function setLight($light)
    {
        $this->light = $light;
    }

    /**
     * @return int
     */
    public function getHsvSaturation()
    {
        return $this->hsvSaturation;
    }

    /**
     * @param int $hsvSaturation
     */
    public function setHsvSaturation($hsvSaturation)
    {
        $this->hsvSaturation = $hsvSaturation;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'name';
    }
}

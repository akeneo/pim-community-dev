<?php

namespace Acme\Bundle\AppBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractReferenceData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;

/**
 * Acme Color entity (used as simple reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Color extends AbstractReferenceData implements ReferenceDataInterface
{
    protected ?string $name = null;

    protected ?string $hex = null;

    protected ?int $red = null;

    protected ?int $green = null;

    protected ?int $blue = null;

    protected ?int $hue = null;

    protected ?int $hslSaturation = null;

    protected ?int $light = null;

    protected ?int $hsvSaturation = null;

    protected ?int $value = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getHex(): ?string
    {
        return $this->hex;
    }

    /**
     * @param string $hex
     */
    public function setHex(string $hex): void
    {
        $this->hex = $hex;
    }

    public function getRed(): ?int
    {
        return $this->red;
    }

    /**
     * @param int $red
     */
    public function setRed(int $red): void
    {
        $this->red = $red;
    }

    public function getGreen(): ?int
    {
        return $this->green;
    }

    /**
     * @param int $green
     */
    public function setGreen(int $green): void
    {
        $this->green = $green;
    }

    public function getBlue(): ?int
    {
        return $this->blue;
    }

    /**
     * @param int $blue
     */
    public function setBlue(int $blue): void
    {
        $this->blue = $blue;
    }

    public function getHue(): ?int
    {
        return $this->hue;
    }

    /**
     * @param int $hue
     */
    public function setHue(int $hue): void
    {
        $this->hue = $hue;
    }

    public function getHslSaturation(): ?int
    {
        return $this->hslSaturation;
    }

    /**
     * @param int $hslSaturation
     */
    public function setHslSaturation(int $hslSaturation): void
    {
        $this->hslSaturation = $hslSaturation;
    }

    public function getLight(): ?int
    {
        return $this->light;
    }

    /**
     * @param int $light
     */
    public function setLight(int $light): void
    {
        $this->light = $light;
    }

    public function getHsvSaturation(): ?int
    {
        return $this->hsvSaturation;
    }

    /**
     * @param int $hsvSaturation
     */
    public function setHsvSaturation(int $hsvSaturation): void
    {
        $this->hsvSaturation = $hsvSaturation;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty(): ?string
    {
        return 'name';
    }
}

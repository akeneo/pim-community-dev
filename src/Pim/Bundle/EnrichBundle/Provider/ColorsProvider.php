<?php

namespace Pim\Bundle\EnrichBundle\Provider;

/**
 * Provides colors based on config
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColorsProvider
{
    /** @staticvar string */
    const COLOR_TRANSLATION_PREFIX = 'color';

    /** @staticvar float */
    const RED_WEIGHT   = 0.229;
    /** @staticvar float */
    const GREEN_WEIGHT = 0.587;
    /** @staticvar float */
    const BLUE_WEIGHT  = 0.114;

    /** @staticvar string */
    const COLOR_BLACK = '#111';
    /** @staticvar string */
    const COLOR_WHITE = '#fff';

    /**
     * @var string[]
     */
    protected $colorsConfig;

    /**
     * Constructor
     *
     * @param array $colorsConfig
     */
    public function __construct($colorsConfig)
    {
        $this->colorsConfig = $colorsConfig;
    }

    /**
     * Get color choices
     * Returns the choices in the format ['blue' => 'color.blue', 'red' => 'color.red']
     * where key is the code and value is a translatable label
     *
     * @return array
     */
    public function getColorChoices()
    {
        $choices = [];
        foreach (array_keys($this->colorsConfig) as $color) {
            $choices[$color] = sprintf('%s.%s', self::COLOR_TRANSLATION_PREFIX, $color);
        }

        return $choices;
    }

    /**
     * Get color code
     * Returns a code for a given color in the format '12,22,221,0.6'
     * where the comma-separated numbers represent red, green, blue and opacity
     *
     * @param string $color
     *
     * @return string
     */
    public function getColorCode($color)
    {
        return isset($this->colorsConfig[$color]) ? $this->colorsConfig[$color] : '';
    }

    /**
     * Get font color for a color code
     * Returns a white or black font color code based on the approximate human-perceived 'darkness'
     * or 'lightness' of the color
     *
     * @param string $color
     *
     * @return string
     */
    public function getFontColor($color)
    {
        $colorCode = $this->getColorCode($color);
        if (!$colorCode) {
            return '';
        }
        list($red, $green, $blue) = explode(',', $colorCode);

        $index = ($red * self::RED_WEIGHT + $blue * self::BLUE_WEIGHT + $green * self::GREEN_WEIGHT)/255;

        return $index > 0.5 ? self::COLOR_BLACK : self::COLOR_WHITE;
    }
}

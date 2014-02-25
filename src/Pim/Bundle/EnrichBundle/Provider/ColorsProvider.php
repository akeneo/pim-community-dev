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
    /**
     * @staticvar string
     */
    const COLOR_TRANSLATION_PREFIX = 'color';

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
}

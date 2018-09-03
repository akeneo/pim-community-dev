<?php

namespace Akeneo\Tool\Component\Localization\Factory;

/**
 * The NumberFactory create instances of NumberFormatter, taking account of predefined formats.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFactory
{
    /** @var array */
    protected $numberFormats;

    /**
     * @param array $numberFormats
     */
    public function __construct(array $numberFormats)
    {
        $this->numberFormats = $numberFormats;
    }

    /**
     * Creates a number formatter according to options and with predefined formats.
     *
     * @param array $options
     *
     * @return \NumberFormatter
     */
    public function create(array $options)
    {
        $options = $this->resolve($options);

        $formatter = new \NumberFormatter($options['locale'], $options['type']);

        if (null !== $options['number_format']) {
            $formatter->setPattern($options['number_format']);
        }

        return $formatter;
    }

    /**
     * Resolve the options for the factory instances.
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolve(array $options)
    {
        $options = array_merge(
            [
                'locale'        => 'en',
                'type'          => \NumberFormatter::DECIMAL,
                'number_format' => null
            ],
            $options
        );

        if (null === $options['number_format']
            && isset($options['locale'])
            && isset($this->numberFormats[$options['locale']])
        ) {
            $options['number_format'] = $this->numberFormats[$options['locale']];
        }

        return $options;
    }
}

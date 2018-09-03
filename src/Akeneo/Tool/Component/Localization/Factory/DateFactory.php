<?php

namespace Akeneo\Tool\Component\Localization\Factory;

/**
 * Create a new instance of IntlDateFormatter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFactory
{
    const TYPE_DATE = \IntlDateFormatter::SHORT;
    const TYPE_TIME = \IntlDateFormatter::NONE;

    /** @var array */
    protected $dateFormats;

    /**
     * @param array $dateFormats
     */
    public function __construct(array $dateFormats)
    {
        $this->dateFormats = $dateFormats;
    }

    /**
     * @param array $options
     *
     * @return \IntlDateFormatter
     */
    public function create(array $options = [])
    {
        $options = $this->resolveOptions($options);

        return new \IntlDateFormatter(
            $options['locale'],
            $options['datetype'],
            $options['timetype'],
            $options['timezone'],
            $options['calendar'],
            $options['date_format']
        );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        if (!isset($options['date_format']) &&
            isset($options['locale']) &&
            isset($this->dateFormats[$options['locale']])
        ) {
            $options['date_format'] = $this->dateFormats[$options['locale']];
        }

        $options = array_merge([
            'locale'      => 'en',
            'datetype'    => static::TYPE_DATE,
            'timetype'    => static::TYPE_TIME,
            'timezone'    => null,
            'calendar'    => null,
            'date_format' => null,
        ], $options);

        return $options;
    }
}

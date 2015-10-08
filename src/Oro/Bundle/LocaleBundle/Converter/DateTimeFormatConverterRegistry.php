<?php

namespace Oro\Bundle\LocaleBundle\Converter;

use Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterInterface;

class DateTimeFormatConverterRegistry
{
    /**
     * @var DateTimeFormatConverterInterface[]
     */
    protected $converters = array();

    /**
     * @param string $name
     * @param DateTimeFormatConverterInterface $converter
     * @throws \LogicException
     */
    public function addFormatConverter($name, DateTimeFormatConverterInterface $converter)
    {
        if (isset($this->converters[$name])) {
            throw new \LogicException(
                sprintf('Format converter with name "%s" already registered', $name)
            );
        }

        $this->converters[$name] = $converter;
    }

    /**
     * @param string $name
     * @return DateTimeFormatConverterInterface
     * @throws \LogicException
     */
    public function getFormatConverter($name)
    {
        if (!isset($this->converters[$name])) {
            throw new \LogicException(
                sprintf('Format converter with name "%s" is not exist', $name)
            );
        }

        return $this->converters[$name];
    }

    /**
     * @return DateTimeFormatConverterInterface[]
     */
    public function getFormatConverters()
    {
        return $this->converters;
    }
}

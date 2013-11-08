<?php

namespace Oro\Bundle\LocaleBundle\Converter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class IntlDateTimeFormatConverter extends AbstractDateTimeFormatConverter
{
    const NAME = 'intl';

    /**
     * Returns INTL format without convert
     *
     * {@inheritDoc}
     */
    protected function convertFormat($format)
    {
        return $format;
    }
}

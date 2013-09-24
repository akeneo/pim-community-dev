<?php

namespace Oro\Bundle\LocaleBundle\Datagrid\Formatter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\GridBundle\Property\FormatterInterface;

class DateTime implements FormatterInterface
{
    const CONFIG_DATE_PARAM_NAME = 'oro_locale.date_format';
    const CONFIG_TIME_PARAM_NAME = 'oro_locale.time_format';

    /** @var \Oro\Bundle\ConfigBundle\Config\ConfigManager */
    protected $cm;

    public function __construct(ConfigManager $cm)
    {
        $this->cm = $cm;
    }

    /**
     * {@inheritdoc}
     */
    public function format($value)
    {
        $dateFormat = $this->cm->get(self::CONFIG_DATE_PARAM_NAME);
        $timeFormat = $this->cm->get(self::CONFIG_TIME_PARAM_NAME);

        $format = $dateFormat . 'T' . $timeFormat;
        if (!$dateFormat || ! $timeFormat) {
            $format = \DateTime::ISO8601;
        }

        return $value->format($format);
    }
}

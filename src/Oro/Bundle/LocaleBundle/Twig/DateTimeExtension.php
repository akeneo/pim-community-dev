<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;

class DateTimeExtension extends \Twig_Extension
{
    /**
     * @var DateTimeFormatter
     */
    protected $formatter;

    /**
     * @param DateTimeFormatter $formatter
     */
    public function __construct(DateTimeFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'oro_format_datetime',
                [$this, 'formatDateTime'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'oro_format_date',
                [$this, 'formatDate'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'oro_format_time',
                [$this, 'formatTime'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Formats date time according to locale settings.
     *
     * Options format:
     * array(
     *     'dateType' => <dateType>,
     *     'timeType' => <timeType>,
     *     'locale' => <locale>,
     *     'timezone' => <timezone>,
     * )
     *
     * @param \DateTime|string|int $date
     * @param array $options
     * @return string
     */
    public function formatDateTime($date, array $options = [])
    {
        $dateType = $this->getOption($options, 'dateType');
        $timeType = $this->getOption($options, 'timeType');
        $locale = $this->getOption($options, 'locale');
        $timeZone = $this->getOption($options, 'timeZone');

        return $this->formatter->format($date, $dateType, $timeType, $locale, $timeZone);
    }

    /**
     * Formats date time according to locale settings.
     *
     * Options format:
     * array(
     *     'dateType' => <dateType>,
     *     'locale' => <locale>,
     *     'timeZone' => <timeZone>,
     * )
     *
     * @param \DateTime|string|int $date
     * @param array $options
     * @return string
     */
    public function formatDate($date, array $options = [])
    {
        $dateType = $this->getOption($options, 'dateType');
        $locale = $this->getOption($options, 'locale');
        $timeZone = $this->getOption($options, 'timeZone');

        return $this->formatter->formatDate($date, $dateType, $locale, $timeZone);
    }

    /**
     * Formats date time according to locale settings.
     *
     * Options format:
     * array(
     *     'timeType' => <timeType>,
     *     'locale' => <locale>,
     *     'timeZone' => <timeZone>,
     * )
     *
     * @param \DateTime|string|int $date
     * @param array $options
     * @return string
     */
    public function formatTime($date, array $options = [])
    {
        $timeType = $this->getOption($options, 'timeType');
        $locale = $this->getOption($options, 'locale');
        $timeZone = $this->getOption($options, 'timeZone');

        return $this->formatter->formatTime($date, $timeType, $locale, $timeZone);
    }

    /**
     * Gets option or default value if option not exist
     *
     * @param array $options
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getOption(array $options, $name, $default = null)
    {
        return isset($options[$name]) ? $options[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale_datetime';
    }
}

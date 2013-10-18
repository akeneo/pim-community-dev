<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;

class NumberExtension extends \Twig_Extension
{
    /**
     * @var NumberFormatter
     */
    protected $formatter;

    /**
     * @param NumberFormatter $formatter
     */
    public function __construct(NumberFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'oro_format_number' => new \Twig_Filter_Method(
                $this,
                'format',
                array('is_safe' => array('html'))
            ),
            'oro_format_currency' => new \Twig_Filter_Method(
                $this,
                'formatCurrency',
                array('is_safe' => array('html'))
            ),
            'oro_format_decimal' => new \Twig_Filter_Method(
                $this,
                'formatDecimal',
                array('is_safe' => array('html'))
            ),
            'oro_format_percent' => new \Twig_Filter_Method(
                $this,
                'formatPercent',
                array('is_safe' => array('html'))
            ),
            'oro_format_spellout' => new \Twig_Filter_Method(
                $this,
                'formatSpellout',
                array('is_safe' => array('html'))
            ),
            'oro_format_duration' => new \Twig_Filter_Method(
                $this,
                'formatDuration',
                array('is_safe' => array('html'))
            ),
            'oro_format_ordinal' => new \Twig_Filter_Method(
                $this,
                'formatOrdinal',
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * Formats number according to locale settings.
     *
     * Options format:
     * array(
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param int|float $value
     * @param int|string $style
     * @param array $options
     * @return string
     */
    public function format($value, $style, array $options = array())
    {
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->format($value, $style, $attributes, $textAttributes, $locale);
    }

    /**
     * Formats currency number according to locale settings.
     *
     * Options format:
     * array(
     *     'currency' => <currency>,
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param float $value
     * @param array $options
     * @return string
     */
    public function formatCurrency($value, array $options = array())
    {
        $currency = $this->getOption($options, 'currency');
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatCurrency($value, $currency, $attributes, $textAttributes, $locale);
    }

    /**
     * Formats decimal number according to locale settings.
     *
     * Options format:
     * array(
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param float $value
     * @param array $options
     * @return string
     */
    public function formatDecimal($value, array $options = array())
    {
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatDecimal($value, $attributes, $textAttributes, $locale);
    }

    /**
     * Formats percent number according to locale settings.
     *
     * Options format:
     * array(
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param float $value
     * @param array $options
     * @return string
     */
    public function formatPercent($value, array $options = array())
    {
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatPercent($value, $attributes, $textAttributes, $locale);
    }

    /**
     * Formats spellout number according to locale settings.
     *
     * Options format:
     * array(
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param float $value
     * @param array $options
     * @return string
     */
    public function formatSpellout($value, array $options = array())
    {
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatSpellout($value, $attributes, $textAttributes, $locale);
    }

    /**
     * Formats duration number according to locale settings.
     *
     * Options format:
     * array(
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param float $value
     * @param array $options
     * @return string
     */
    public function formatDuration($value, array $options = array())
    {
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatDuration($value, $attributes, $textAttributes, $locale);
    }

    /**
     * Formats ordinal number according to locale settings.
     *
     * Options format:
     * array(
     *     'attributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'textAttributes' => array(
     *          <attribute> => <value>,
     *          ...
     *      ),
     *     'locale' => <locale>
     * )
     *
     * @param float $value
     * @param array $options
     * @return string
     */
    public function formatOrdinal($value, array $options = array())
    {
        $attributes = (array)$this->getOption($options, 'attributes', array());
        $textAttributes = (array)$this->getOption($options, 'textAttributes', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatOrdinal($value, $attributes, $textAttributes, $locale);
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
        return 'oro_locale_number';
    }
}

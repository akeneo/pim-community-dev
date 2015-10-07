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
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'oro_locale_number_attribute',
                array($this, 'getAttribute'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'oro_locale_number_text_attribute',
                array($this, 'getTextAttribute'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'oro_locale_number_symbol',
                array($this, 'getSymbol'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'oro_format_number',
                array($this, 'format'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'oro_format_currency',
                array($this, 'formatCurrency'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'oro_format_decimal',
                array($this, 'formatDecimal'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'oro_format_percent',
                array($this, 'formatPercent'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'oro_format_spellout',
                array($this, 'formatSpellout'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'oro_format_duration',
                array($this, 'formatDuration'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'oro_format_ordinal',
                array($this, 'formatOrdinal'),
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * Gets value of numeric attribute of \NumberFormatter
     *
     * @param string|int $attribute
     * @param string|null $style
     * @param string|null $locale
     * @return int
     */
    public function getAttribute($attribute, $style = null, $locale = null)
    {
        return $this->formatter->getAttribute($attribute, $style, $locale);
    }

    /**
     * Gets value of text attribute of \NumberFormatter
     *
     * @param string|int $attribute
     * @param string|null $style
     * @param string|null $locale
     * @return string
     */
    public function getTextAttribute($attribute, $style = null, $locale = null)
    {
        return $this->formatter->getTextAttribute($attribute, $style, $locale);
    }

    /**
     * Gets value of symbol associated with \NumberFormatter
     *
     * @param string|int $symbol
     * @param string|null $style
     * @param string|null $locale
     * @return string
     */
    public function getSymbol($symbol, $style = null, $locale = null)
    {
        return $this->formatter->getSymbol($symbol, $style, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->format($value, $style, $attributes, $textAttributes, $symbols, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatCurrency($value, $currency, $attributes, $textAttributes, $symbols, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatDecimal($value, $attributes, $textAttributes, $symbols, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatPercent($value, $attributes, $textAttributes, $symbols, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatSpellout($value, $attributes, $textAttributes, $symbols, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatDuration($value, $attributes, $textAttributes, $symbols, $locale);
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
     *     'symbols' => array(
     *          <symbol> => <value>,
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
        $symbols = (array)$this->getOption($options, 'symbols', array());
        $locale = $this->getOption($options, 'locale');

        return $this->formatter->formatOrdinal($value, $attributes, $textAttributes, $symbols, $locale);
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

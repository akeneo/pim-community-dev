<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;

/**
 * Twig extension to render locales from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtension extends \Twig_Extension
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * Constructor
     *
     * @param LocaleHelper $localeHelper
     */
    public function __construct(LocaleHelper $localeHelper)
    {
        $this->localeHelper = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'locale_label' => new \Twig_Function_Method($this, 'localeLabel'),
            'currency_symbol' => new \Twig_Function_Method($this, 'currencySymbol'),
            'locale_currency' => new \Twig_Function_Method($this, 'localeCurrency'),
            'currency_label'  => new \Twig_Function_Method($this, 'currencyLabel')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'flag' => new \Twig_Filter_Method($this, 'flag', ['is_safe' => ['html']]),
        ];
    }

    /**
     * Get displayed locale from locale code
     *
     * @param string $code
     * @param string $locale
     *
     * @return string
     */
    public function localeLabel($code, $locale = null)
    {
        return $this->localeHelper->getLocaleLabel($code, $locale);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $code
     * @param string $locale
     *
     * @return string
     */
    public function currencySymbol($code, $locale = null)
    {
        return $this->localeHelper->getCurrencySymbol($code, $locale);
    }

    /**
     * Returns the catalog locale currency
     *
     * @return string
     */
    public function localeCurrency()
    {
        return $this->localeHelper->getLocaleCurrency();
    }

    /**
     * Returns the currency label
     *
     * @param string $code
     * @param string $locale
     *
     * @return string
     */
    public function currencyLabel($code, $locale = null)
    {
        return $this->localeHelper->getCurrencyLabel($code, $locale);
    }

    /**
     * Returns the flag icon for a locale
     *
     * @param string $code
     * @param string $locale
     *
     * @return string
     */
    public function flag($code, $locale = null)
    {
        return $this->localeHelper->getFlag($code, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_locale_extension';
    }
}

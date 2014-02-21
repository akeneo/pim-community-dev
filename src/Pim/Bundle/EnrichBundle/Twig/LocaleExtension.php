<?php

namespace Pim\Bundle\EnrichBundle\Twig;

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
        return array(
            'locale_code'     => new \Twig_Function_Method($this, 'currentLocaleCode'),
            'locale_label'    => new \Twig_Function_Method($this, 'localeLabel'),
            'currency_symbol' => new \Twig_Function_Method($this, 'currencySymbol'),
            'currency_label'  => new \Twig_Function_Method($this, 'currencyLabel')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'flag' => new \Twig_Filter_Method($this, 'flag', array('is_safe' => array('html'))),
        );
    }

    /**
     * Get current locale code
     *
     * @return string
     */
    public function currentLocaleCode()
    {
        return $this->localeHelper->getCurrentLocaleCode();
    }

    /**
     * Get displayed locale from locale code
     *
     * @param string $code
     * @param string $localeCode
     *
     * @return string
     */
    public function localeLabel($code, $localeCode = null)
    {
        return $this->localeHelper->getLocaleLabel($code, $localeCode);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $code
     * @param string $localeCode
     *
     * @return string
     */
    public function currencySymbol($code, $localeCode = null)
    {
        return $this->localeHelper->getCurrencySymbol($code, $localeCode);
    }

    /**
     * Returns the currency label
     *
     * @param string $code
     * @param string $localeCode
     *
     * @return string
     */
    public function currencyLabel($code, $localeCode = null)
    {
        return $this->localeHelper->getCurrencyLabel($code, $localeCode);
    }

    /**
     * Returns the flag icon for a locale
     *
     * @param string $code
     * @param string $localeCode
     *
     * @return string
     */
    public function flag($code, $localeCode = null)
    {
        return $this->localeHelper->getFlag($code, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_locale_extension';
    }
}

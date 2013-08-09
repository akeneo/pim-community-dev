<?php

namespace Pim\Bundle\ProductBundle\Twig;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Locale\Locale;
use Symfony\Component\Locale\Stub\StubLocale;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

/**
 * Display currency symbol from code
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExtension extends \Twig_Extension
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @param SecurityContextInterface $securityContext
     * @param LocaleManager            $localeManager
     */
    public function __construct(SecurityContextInterface $securityContext, LocaleManager $localeManager)
    {
        $this->securityContext = $securityContext;
        $this->localeManager   = $localeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'currencySymbol' => new \Twig_Function_Method($this, 'currencySymbol'),
            'localeLabel'    => new \Twig_Function_Method($this, 'localeLabel'),
            'localeCurrency' => new \Twig_Function_Method($this, 'localeCurrency'),
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'flag' => new \Twig_Filter_Method($this, 'flag', array('is_safe' => array('html'))),
        );
    }

    /**
     * Get currency symbol from code
     *
     * @param string $currency
     *
     * @return string
     */
    public function currencySymbol($currency)
    {
        $currencies = StubLocale::getCurrenciesData('en');

        return (isset($currencies[$currency])) ? $currencies[$currency]['symbol'] : null;
    }

    /**
     * Get displayed locale from locale code
     *
     * @param string $code
     *
     * @return string
     */
    public function localeLabel($code)
    {
        if ($catalogLocale = $this->getCatalogLocale()) {
            $countries = Locale::getDisplayLocales($catalogLocale);

            if (isset($countries[$code])) {
                return $countries[$code];
            }

            list($lang) = explode('_', $code);
            if (isset($countries[$lang])) {
                return $countries[$lang];
            }
        }

        return $code;
    }

    /**
     * Get locale currency
     *
     * @return string
     */
    public function localeCurrency()
    {
        return $this->getCatalogCurrency();
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function flag($code)
    {
        return sprintf(
            '<img src="%s" class="flag flag-%s" alt="%s" /><code class="flag-language">%s</code>',
            '/bundles/pimui/images/blank.gif',
            $this->getCountry($code),
            $this->localeLabel($code),
            $this->getLanguage($code)
        );
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getCountry($code)
    {
        $parts = explode('_', $code);
        if (isset($parts[1])) {
            return strtolower($parts[1]);
        }

        return 'unknown';
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getLanguage($code)
    {
        list($language) = explode('_', $code);

        return $language;
    }

    /**
     * @return string|NULL
     */
    private function getCatalogLocale()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return (string) $user->cataloglocale;
    }

    /**
     * @return string|NULL
     */
    private function getCatalogCurrency()
    {
        $localeCode = $this->getCatalogLocale();
        $locale = $this->localeManager->getLocaleByCode($localeCode);

        return ($locale !== null) ? $locale->getDefaultCurrency()->getCode() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_extension';
    }
}

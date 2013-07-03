<?php

namespace Pim\Bundle\ProductBundle\Twig;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Locale\Locale;
use Symfony\Component\Locale\Stub\StubLocale;

/**
 * Display currency symbol from code
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExtension extends \Twig_Extension
{
    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'currencySymbol' => new \Twig_Function_Method($this, 'currencySymbolFunction'),
            'localeLabel'    => new \Twig_Function_Method($this, 'localeLabel'),
        );
    }

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
    public function currencySymbolFunction($currency)
    {
        $currencies = StubLocale::getCurrenciesData('en');

        return (isset($currencies[$currency])) ? $currencies[$currency]['symbol'] : null;
    }

    /**
     * Get displayed locale from locale code
     *
     * @param string $locale
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

    public function flag($code)
    {
        return sprintf(
            '<img class="flag flag-%s" alt="%s" />',
            $this->getCountry($code),
            $this->localeLabel($code)
        );
    }

    private function getCountry($code)
    {
        $parts = explode('_', $code);
        if (isset($parts[1])) {
            return strtolower($parts[1]);
        }

        return 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_extension';
    }

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
}

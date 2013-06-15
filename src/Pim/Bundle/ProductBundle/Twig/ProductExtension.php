<?php
namespace Pim\Bundle\ProductBundle\Twig;

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
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'currencySymbol' => new \Twig_Function_Method($this, 'currencySymbolFunction'),
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_extension';
    }
}

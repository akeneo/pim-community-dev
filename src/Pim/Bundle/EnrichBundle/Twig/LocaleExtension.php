<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use \Twig_Environment;
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
    /** @var LocaleHelper */
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
            new \Twig_SimpleFunction('locale_code', [$this, 'currentLocaleCode']),
            new \Twig_SimpleFunction('locale_label', [$this, 'localeLabel']),
            new \Twig_SimpleFunction('currency_symbol', [$this, 'currencySymbol']),
            new \Twig_SimpleFunction('currency_label', [$this, 'currencyLabel'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'flag',
                [$this, 'flag'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
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
     * Get displayed locale from locale code translated in the specific language
     *
     * @param string $code
     * @param string $translateIn
     *
     * @return string
     */
    public function localeLabel($code, $translateIn = null)
    {
        return $this->localeHelper->getLocaleLabel($code, $translateIn);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $code
     * @param string $translateIn
     *
     * @return string
     */
    public function currencySymbol($code, $translateIn = null)
    {
        return $this->localeHelper->getCurrencySymbol($code, $translateIn);
    }

    /**
     * Returns the currency label
     *
     * @param string $code
     * @param string $translateIn
     *
     * @return string
     */
    public function currencyLabel($code, $translateIn = null)
    {
        return $this->localeHelper->getCurrencyLabel($code, $translateIn);
    }

    /**
     * Returns the flag icon for a locale with its country as long label or short code
     *
     * @param Twig_Environment $environment
     * @param string           $code
     * @param boolean          $short
     * @param string           $translateIn
     *
     * @return string
     */
    public function flag(Twig_Environment $environment, $code, $short = true, $translateIn = null)
    {
        return $environment->render(
            'PimEnrichBundle:Locale:_flag.html.twig',
            [
                'label' => $this->localeHelper->getLocaleLabel($code, $translateIn),
                'region' => $this->localeHelper->getRegion($code),
                'language' => $this->localeHelper->getLanguage($code),
                'short' => $short,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_locale_extension';
    }
}

<?php

namespace Akeneo\Channel\Bundle\Twig;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Intl;
use Twig_Environment;

/**
 * Twig extension to render locales from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtension extends \Twig_Extension
{
    /** @var UserContext */
    protected $userContext;

    /**
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
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
                    'is_safe'           => ['html'],
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
        return $this->userContext->getCurrentLocale()->getCode();
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
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $translateIn);
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
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($code, $language);
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
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($code, $language);
    }

    /**
     * Returns the flag icon for a locale with its country as long label or short code
     *
     * @param Twig_Environment $environment
     * @param string           $code
     * @param bool             $short
     * @param string           $translateIn
     *
     * @return string
     */
    public function flag(Twig_Environment $environment, $code, $short = true, $translateIn = null)
    {
        return $environment->render(
            'PimUIBundle:Locale:_flag.html.twig',
            [
                'label'    => $this->localeLabel($code, $translateIn),
                'region'   => \Locale::getRegion($code),
                'language' => \Locale::getPrimaryLanguage($code),
                'short'    => $short,
            ]
        );
    }

    /**
     * Returns the current locale code
     *
     * @return string
     */
    private function getCurrentLocaleCode()
    {
        return $this->userContext->getCurrentLocale()->getCode();
    }
}

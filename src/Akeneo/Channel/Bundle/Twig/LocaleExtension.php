<?php

namespace Akeneo\Channel\Bundle\Twig;

use Symfony\Component\Intl\Intl;
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
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('locale_code', fn() => $this->currentLocaleCode()),
            new \Twig_SimpleFunction('locale_label', fn($code, $translateIn = null) => $this->localeLabel($code, $translateIn)),
            new \Twig_SimpleFunction('currency_symbol', fn($code, $translateIn = null) => $this->currencySymbol($code, $translateIn)),
            new \Twig_SimpleFunction('currency_label', fn($code, $translateIn = null) => $this->currencyLabel($code, $translateIn))
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter(
                'flag',
                fn(Twig_Environment $environment, $code, $short = true, $translateIn = null) => $this->flag($environment, $code, $short, $translateIn),
                [
                    'is_safe'           => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    /**
     * Get current locale code
     */
    public function currentLocaleCode(): string
    {
        return $this->userContext->getCurrentLocale()->getCode();
    }

    /**
     * Get displayed locale from locale code translated in the specific language
     *
     * @param string $code
     * @param string $translateIn
     */
    public function localeLabel(string $code, string $translateIn = null): string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $translateIn);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $code
     * @param string $translateIn
     */
    public function currencySymbol(string $code, string $translateIn = null): ?string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl::getCurrencyBundle()->getCurrencySymbol($code, $language);
    }

    /**
     * Returns the currency label
     *
     * @param string $code
     * @param string $translateIn
     */
    public function currencyLabel(string $code, string $translateIn = null): ?string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl::getCurrencyBundle()->getCurrencyName($code, $language);
    }

    /**
     * Returns the flag icon for a locale with its country as long label or short code
     *
     * @param Twig_Environment $environment
     * @param string           $code
     * @param bool             $short
     * @param string           $translateIn
     */
    public function flag(Twig_Environment $environment, string $code, bool $short = true, string $translateIn = null): string
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
     */
    private function getCurrentLocaleCode(): string
    {
        return $this->userContext->getCurrentLocale()->getCode();
    }
}

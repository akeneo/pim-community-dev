<?php

namespace Akeneo\Channel\Bundle\Twig;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Locale;
use Symfony\Component\Intl;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig extension to render locales from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtension extends AbstractExtension
{
    protected UserContext $userContext;

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
            new TwigFunction('locale_code', [$this, 'currentLocaleCode']),
            new TwigFunction('locale_label', [$this, 'localeLabel']),
            new TwigFunction('currency_symbol', [$this, 'currencySymbol']),
            new TwigFunction('currency_label', [$this, 'currencyLabel']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'flag',
                [$this, 'flag'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    public function currentLocaleCode(): string
    {
        return $this->userContext->getCurrentLocale()->getCode();
    }

    /**
     * Get displayed locale from locale code translated in the specific language
     */
    public function localeLabel(string $code, ?string $translateIn = null): string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();

        return Locale::getDisplayName($code, $translateIn);
    }

    public function currencySymbol(string $code, ?string $translateIn = null): string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($code, $language);
    }

    public function currencyLabel(string $code, ?string $translateIn = null): string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($code, $language);
    }

    /**
     * Returns the flag icon for a locale with its country as long label or short code
     */
    public function flag(
        Environment $environment,
        string $code,
        bool $short = true,
        ?string $translateIn = null
    ): string {
        return $environment->render(
            'PimUIBundle:Locale:_flag.html.twig',
            [
                'label' => $this->localeLabel($code, $translateIn),
                'region' => Locale::getRegion($code),
                'language' => Locale::getPrimaryLanguage($code),
                'short' => $short,
            ]
        );
    }

    private function getCurrentLocaleCode(): string
    {
        return $this->userContext->getCurrentLocale()->getCode();
    }
}

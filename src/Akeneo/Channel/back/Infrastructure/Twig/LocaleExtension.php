<?php

namespace Akeneo\Channel\Infrastructure\Twig;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Intl;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Exception\MissingResourceException;
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
    public function getFunctions()
    {
        return [
            new TwigFunction('locale_code', [$this, 'currentLocaleCode']),
            new TwigFunction('locale_label', [$this, 'localeLabel']),
            new TwigFunction('currency_symbol', [$this, 'currencySymbol']),
            new TwigFunction('currency_label', [$this, 'currencyLabel'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
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

        return Currencies::getSymbol($code, $language);
    }

    public function currencyLabel(string $code, ?string $translateIn = null): ?string
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        try {
            return Currencies::getName($code, $language);
        } catch (MissingResourceException) {
            return null;
        }
    }

    /**
     * Returns the flag icon for a locale with its country as long label or short code
     *
     * @param Environment      $environment
     * @param string           $code
     * @param bool             $short
     * @param string           $translateIn
     *
     * @return string
     */
    public function flag(Environment $environment, $code, $short = true, $translateIn = null)
    {
        return $environment->render(
            '@PimUI/Locale/_flag.html.twig',
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

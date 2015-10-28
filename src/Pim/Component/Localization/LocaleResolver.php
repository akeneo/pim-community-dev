<?php

namespace Pim\Component\Localization;

use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Resolves the format depending on the user's locale
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleResolver
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var FormatProviderInterface */
    protected $dateFormatProvider;

    /** @var FormatProviderInterface */
    protected $numberFormatProvider;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param RequestStack            $requestStack
     * @param FormatProviderInterface $dateFormatProvider
     * @param FormatProviderInterface $numberFormatProvider
     * @param string                  $defaultLocale
     */
    public function __construct(
        RequestStack $requestStack,
        FormatProviderInterface $dateFormatProvider,
        FormatProviderInterface $numberFormatProvider,
        $defaultLocale
    ) {
        $this->requestStack         = $requestStack;
        $this->dateFormatProvider   = $dateFormatProvider;
        $this->numberFormatProvider = $numberFormatProvider;
        $this->defaultLocale        = $defaultLocale;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        $locale = $this->getCurrentLocale();

        return [
            'decimal_separator' => $this->numberFormatProvider->getFormat($locale)['decimal_separator'],
            'date_format'       => $this->dateFormatProvider->getFormat($locale),
        ];
    }

    /**
     * Get current locale. If request is null, take the default locale defined in config
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $this->defaultLocale;
        }

        return $request->getLocale();
    }
}

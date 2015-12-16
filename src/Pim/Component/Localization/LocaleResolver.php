<?php

namespace Pim\Component\Localization;

use Pim\Component\Localization\Factory\DateFactory;
use Pim\Component\Localization\Factory\NumberFactory;
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

    /** @var DateFactory */
    protected $dateFactory;

    /** @var NumberFactory */
    protected $numberFactory;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param RequestStack  $requestStack
     * @param DateFactory   $dateFactory
     * @param NumberFactory $numberFactory
     * @param string        $defaultLocale
     */
    public function __construct(
        RequestStack $requestStack,
        DateFactory $dateFactory,
        NumberFactory $numberFactory,
        $defaultLocale
    ) {
        $this->requestStack  = $requestStack;
        $this->dateFactory   = $dateFactory;
        $this->numberFactory = $numberFactory;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        $options = ['locale' => $this->getCurrentLocale()];
        $decimalSeparator = $this->numberFactory->create($options)
            ->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        return [
            'decimal_separator' => $decimalSeparator,
            'date_format'       => $this->dateFactory->create($options)->getPattern(),
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

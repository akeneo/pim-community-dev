<?php

namespace Pim\Component\Localization\Formatter;

use Pim\Component\Localization\Localizer\AbstractNumberLocalizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * NumberFormatter is used to format numbers according to the current user locale.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFormatter implements FormatterInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function format($value)
    {
        $uiLocale = $this->getUiLocale();
        if (null === $uiLocale) {
            return $value;
        }
        $numberFormatter = new \NumberFormatter($uiLocale, \NumberFormatter::DECIMAL);
        $numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->getDecimalCount($value));

        return $numberFormatter->format($value);
    }

    /**
     * Returns the number of decimals after separator.
     *
     * @param string $value
     *
     * @return int
     */
    protected function getDecimalCount($value)
    {
        $separatorIndex = strpos($value, AbstractNumberLocalizer::DEFAULT_DECIMAL_SEPARATOR);
        if (false === $separatorIndex) {
            return 0;
        }

        return strlen($value) - $separatorIndex - 1;
    }

    /**
     * Returns the UI Locale of current user
     *
     * @return string|null
     */
    protected function getUiLocale()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $currentUser = $token->getUser();
        if (null === $currentUser) {
            return null;
        }

        return $currentUser->getUiLocale()->getLanguage();
    }
}

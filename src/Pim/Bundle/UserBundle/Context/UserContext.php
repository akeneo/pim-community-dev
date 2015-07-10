<?php

namespace Pim\Bundle\UserBundle\Context;

use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * User context that provides access to user locale, channel and default category tree
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserContext
{
    /** @staticvar string */
    const REQUEST_LOCALE_PARAM = 'dataLocale';

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var LocaleManager */
    protected $localeManager;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var CategoryManager */
    protected $categoryManager;

    /** @var Request */
    protected $request;

    /** @var array */
    protected $userLocales;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param LocaleManager         $localeManager
     * @param ChannelManager        $channelManager
     * @param CategoryManager       $categoryManager
     * @param string                $defaultLocale
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        CategoryManager $categoryManager,
        $defaultLocale
    ) {
        $this->tokenStorage    = $tokenStorage;
        $this->localeManager   = $localeManager;
        $this->channelManager  = $channelManager;
        $this->categoryManager = $categoryManager;
        $this->defaultLocale   = $defaultLocale;
    }

    /**
     * Sets the current request
     *
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Returns the current locale from the request or the user's catalog locale
     * or the first activated locale
     *
     * @throws \LogicException When there are no activated locales
     *
     * @return Locale
     */
    public function getCurrentLocale()
    {
        if (null !== $locale = $this->getRequestLocale()) {
            return $locale;
        }

        if (null !== $locale = $this->getUserLocale()) {
            return $locale;
        }

        if (null !== $locale = $this->getDefaultLocale()) {
            return $locale;
        }

        if ($locale = current($this->getUserLocales())) {
            return $locale;
        }

        throw new \LogicException('There are no activated locales');
    }

    /**
     * Returns the current locale code
     *
     * @return string
     */
    public function getCurrentLocaleCode()
    {
        return $this->getCurrentLocale()->getCode();
    }

    /**
     * Returns active locales
     *
     * @return Locale[]
     */
    public function getUserLocales()
    {
        if ($this->userLocales === null) {
            $this->userLocales = $this->localeManager->getActiveLocales();
        }

        return $this->userLocales;
    }

    /**
     * Returns the codes of active locales
     *
     * @return array
     */
    public function getUserLocaleCodes()
    {
        return array_map(
            function ($locale) {
                return $locale->getCode();
            },
            $this->getUserLocales()
        );
    }

    /**
     * Get user channel
     *
     * @return Channel
     */
    public function getUserChannel()
    {
        $catalogScope = $this->getUserOption('catalogScope');

        return $catalogScope ?: current($this->channelManager->getChannels());
    }

    /**
     * Get user channel code
     *
     * @return string
     */
    public function getUserChannelCode()
    {
        return $this->getUserChannel()->getCode();
    }

    /**
     * Get channel choices with user channel code first
     *
     * @return string[]
     */
    public function getChannelChoicesWithUserChannel()
    {
        $channelChoices  = $this->channelManager->getChannelChoices();
        $userChannelCode = $this->getUserChannelCode();

        if (array_key_exists($userChannelCode, $channelChoices)) {
            return [$userChannelCode => $channelChoices[$userChannelCode]] + $channelChoices;
        }

        return $channelChoices;
    }

    /**
     * Get user category tree
     *
     * @return CategoryInterface
     */
    public function getUserTree()
    {
        $defaultTree = $this->getUserOption('defaultTree');

        return $defaultTree ?: current($this->categoryManager->getTrees());
    }

    /**
     * Returns the request locale
     *
     * @return Locale|null
     */
    protected function getRequestLocale()
    {
        if ($this->request) {
            $localeCode = $this->request->get(self::REQUEST_LOCALE_PARAM);
            if ($localeCode) {
                $locale = $this->localeManager->getLocaleByCode($localeCode);
                if ($locale && $this->isLocaleAvailable($locale)) {
                    return $locale;
                }
            }
        }

        return null;
    }

    /**
     * Returns the user locale
     *
     * @return Locale|null
     */
    protected function getUserLocale()
    {
        $locale = $this->getUserOption('catalogLocale');

        return $locale && $this->isLocaleAvailable($locale) ? $locale : null;
    }

    /**
     * Returns the default application locale
     *
     * @return Locale|null
     */
    protected function getDefaultLocale()
    {
        return $this->localeManager->getLocaleByCode($this->defaultLocale);
    }

    /**
     * Checks if a locale is activated
     *
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    protected function isLocaleAvailable(LocaleInterface $locale)
    {
        return $locale->isActivated();
    }

    /**
     * Get a user option
     *
     * @param string $optionName
     *
     * @return mixed|null
     */
    protected function getUserOption($optionName)
    {
        $token = $this->tokenStorage->getToken();

        if ($token !== null) {
            $user   = $token->getUser();
            $method = sprintf('get%s', ucfirst($optionName));

            if ($user && is_callable([$user, $method])) {
                $value = $user->$method();
                if ($value) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Get authenticated user
     *
     * @return User|null
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}

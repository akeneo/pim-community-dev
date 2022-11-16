<?php

namespace Akeneo\UserManagement\Bundle\Context;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\FirewallMapInterface;

/**
 * User context that provides access to user locale, channel and default category tree.
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserContext
{
    /** @staticvar string */
    public const REQUEST_LOCALE_PARAM = 'dataLocale';

    /** @staticvar string */
    public const USER_PRODUCT_CATEGORY_TYPE = 'product';

    public function __construct(
        protected Security $security,
        protected LocaleRepositoryInterface $localeRepository,
        protected ChannelRepositoryInterface $channelRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected RequestStack $requestStack,
        protected string $defaultLocale,
        protected FirewallMapInterface $firewall
    ) {
    }

    /**
     * Returns the current locale from the request or the user's catalog locale
     * or the first activated locale.
     *
     * @throws \LogicException When there are no activated locales
     */
    public function getCurrentLocale(): LocaleInterface
    {
        if (null !== $this->currentLocale) {
            return $this->currentLocale;
        }

        $locale = $this->getRequestLocale();

        if (null === $locale) {
            $locale = $this->getSessionLocale();
        }

        if (null === $locale) {
            $locale = $this->getUserLocale();
        }

        if (null === $locale) {
            $locale = $this->getDefaultLocale();
        }

        if (null === $locale) {
            $locale = current($this->getUserLocales());
            $locale = (false === $locale) ? null : $locale;
        }

        if (null === $locale) {
            throw new \LogicException('There are no activated locales');
        }

        if ($this->hasActiveSession($this->getCurrentRequest())) {
            $this->getCurrentRequest()->getSession()->set('dataLocale', $locale->getCode());
            $this->getCurrentRequest()->getSession()->save();
        }

        $this->currentLocale = $locale;

        return $locale;
    }

    private function hasActiveSession(?Request $request): bool
    {
        if (null === $request) {
            return false;
        }

        // The method getFirewallConfig is only part of Symfony\Bundle\SecurityBundle\Security\FirewallMap,
        // not in the FirewallMapInterface.
        // In EE, we override the "@security.firewall.map" service with another class that is not extending
        // Symfony\Bundle\SecurityBundle\Security\FirewallMap but still provide getFirewallConfig.
        if ($this->firewall instanceof FirewallMap || method_exists($this->firewall, 'getFirewallConfig')) {
            $firewallConfig = $this->firewall->getFirewallConfig($request);

            if ($firewallConfig instanceof FirewallConfig && $firewallConfig->isStateless()) {
                return false;
            }
        }

        return $request->hasSession();
    }

    /**
     * Returns the current locale code.
     */
    public function getCurrentLocaleCode(): string
    {
        return $this->getCurrentLocale()->getCode();
    }

    /**
     * Returns active locales.
     *
     * @return LocaleInterface[]
     */
    public function getUserLocales(): array
    {
        if (null === $this->userLocales) {
            $this->userLocales = $this->localeRepository->getActivatedLocales();
        }

        return $this->userLocales;
    }

    /**
     * Returns the codes of active locales.
     */
    public function getUserLocaleCodes(): array
    {
        return array_map(
            function ($locale) {
                return $locale->getCode();
            },
            $this->getUserLocales()
        );
    }

    /**
     * Get user channel.
     */
    public function getUserChannel(): ChannelInterface
    {
        $catalogScope = $this->getUserOption('catalogScope');

        if (null === $catalogScope) {
            throw new \LogicException('No default channel for the user.');
        }

        return $catalogScope;
    }

    /**
     * Get user channel code.
     */
    public function getUserChannelCode(): string
    {
        return $this->getUserChannel()->getCode();
    }

    /**
     * Get channel choices with user channel code first.
     *
     * @return string[]
     */
    public function getChannelChoicesWithUserChannel(): array
    {
        $channels = $this->channelRepository->findAll();
        $userChannelCode = $this->getUserChannelCode();
        $channelChoices = [];

        foreach ($channels as $channel) {
            $channelChoices[$channel->getCode()] = $channel->getLabel();
        }

        if (array_key_exists($userChannelCode, $channelChoices)) {
            return [$userChannelCode => $channelChoices[$userChannelCode]] + $channelChoices;
        }

        return $channelChoices;
    }

    /**
     * For the given $relatedEntity of category asked, return the default user category.
     *
     * @param string $relatedEntity
     */
    public function getUserCategoryTree($relatedEntity): ?CategoryInterface
    {
        if (static::USER_PRODUCT_CATEGORY_TYPE === $relatedEntity) {
            return $this->getUserProductCategoryTree();
        }

        return null;
    }

    /**
     * Get user product category tree.
     */
    public function getUserProductCategoryTree(): CategoryInterface
    {
        $defaultTree = $this->getUserOption('defaultTree');

        return $defaultTree ?: current($this->categoryRepository->getTrees());
    }

    /**
     * Return the current user's timezone.
     *
     * @throws \RuntimeException
     */
    public function getUserTimezone(): string
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \RuntimeException('Impossible to load the current user from context.');
        }

        return $user->getTimezone();
    }

    /**
     * Returns the UI user locale.
     *
     * @return LocaleInterface|null
     */
    public function getUiLocale()
    {
        return $this->getUserOption('uiLocale');
    }

    /**
     * Returns the UI user locale code.
     *
     * @return string
     */
    public function getUiLocaleCode()
    {
        if (null === $uiLocale = $this->getUiLocale()) {
            throw new \LogicException('User has no locale');
        }

        return $uiLocale->getCode();
    }

    /**
     * Get authenticated user.
     */
    public function getUser(): ?UserInterface
    {
        return $this->security->getUser();
    }

    /**
     * Get the user context as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $channels = array_keys($this->getChannelChoicesWithUserChannel());
        $locales = $this->getUserLocaleCodes();

        return [
            'locales' => $locales,
            'channels' => $channels,
            'locale' => $this->getUiLocale()->getCode(),
            'channel' => $this->getUserChannelCode(),
        ];
    }

    /**
     * @return CategoryInterface
     */
    public function getAccessibleUserTree()
    {
        return $this->getUserProductCategoryTree();
    }

    /**
     * Returns the request locale.
     *
     * @return LocaleInterface|null
     */
    protected function getRequestLocale()
    {
        $request = $this->getCurrentRequest();
        if (null !== $request) {
            $localeCode = $request->get(self::REQUEST_LOCALE_PARAM);
            if ($localeCode) {
                $locale = $this->localeRepository->findOneByIdentifier($localeCode);
                if ($locale && $this->isLocaleAvailable($locale)) {
                    return $locale;
                }
            }
        }

        return null;
    }

    /**
     * Returns the user session locale.
     *
     * @return LocaleInterface|null
     */
    protected function getSessionLocale()
    {
        $request = $this->getCurrentRequest();
        if ($this->hasActiveSession($request)) {
            $localeCode = $request->getSession()->get('dataLocale');
            if (null !== $localeCode) {
                $locale = $this->localeRepository->findOneByIdentifier($localeCode);
                if (null !== $locale && $this->isLocaleAvailable($locale)) {
                    return $locale;
                }
            }
        }

        return null;
    }

    /**
     * Returns the catalog user locale.
     *
     * @return LocaleInterface|null
     */
    protected function getUserLocale()
    {
        $locale = $this->getUserOption('catalogLocale');

        return $locale && $this->isLocaleAvailable($locale) ? $locale : null;
    }

    /**
     * Returns the default application locale.
     *
     * @return LocaleInterface|null
     */
    protected function getDefaultLocale()
    {
        return $this->localeRepository->findOneByIdentifier($this->defaultLocale);
    }

    /**
     * Checks if a locale is activated.
     *
     * @return bool
     */
    protected function isLocaleAvailable(LocaleInterface $locale)
    {
        return $locale->isActivated();
    }

    /**
     * Get a user option.
     *
     * @param string $optionName
     *
     * @return mixed|null
     */
    protected function getUserOption($optionName)
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }

        $method = sprintf('get%s', ucfirst($optionName));

        if (null === $user || !is_object($user)) {
            return null;
        }

        if (is_callable([$user, $method])) {
            $value = $user->$method();
            if ($value) {
                return $value;
            }
        } else {
            $value = $user->getProperty($optionName);

            if ($value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get current request.
     *
     * @return Request|null
     */
    protected function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}

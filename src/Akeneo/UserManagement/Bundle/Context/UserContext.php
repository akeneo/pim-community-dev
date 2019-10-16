<?php

namespace Akeneo\UserManagement\Bundle\Context;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /** @staticvar string */
    const USER_PRODUCT_CATEGORY_TYPE = 'product';

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var RequestStack */
    protected $requestStack;

    /** @var array */
    protected $userLocales;

    /** @var LocaleInterface */
    protected $currentLocale;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param TokenStorageInterface       $tokenStorage
     * @param LocaleRepositoryInterface   $localeRepository
     * @param ChannelRepositoryInterface  $channelRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param RequestStack                $requestStack
     * @param string                      $defaultLocale
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        RequestStack $requestStack,
        $defaultLocale
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->categoryRepository= $categoryRepository;
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Returns the current locale from the request or the user's catalog locale
     * or the first activated locale
     *
     * @throws \LogicException When there are no activated locales
     *
     * @return LocaleInterface
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

        if (null !== $this->getCurrentRequest() && $this->getCurrentRequest()->hasSession()) {
            $this->getCurrentRequest()->getSession()->set('dataLocale', $locale->getCode());
            $this->getCurrentRequest()->getSession()->save();
        }

        $this->currentLocale = $locale;

        return $locale;
    }

    /**
     * Returns the current locale code
     *
     * @return string
     */
    public function getCurrentLocaleCode(): string
    {
        return $this->getCurrentLocale()->getCode();
    }

    /**
     * Returns active locales
     *
     * @return LocaleInterface[]
     */
    public function getUserLocales(): array
    {
        if ($this->userLocales === null) {
            $this->userLocales = $this->localeRepository->getActivatedLocales();
        }

        return $this->userLocales;
    }

    /**
     * Returns the codes of active locales
     *
     * @return array
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
     * Get user channel
     *
     * @return ChannelInterface
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
     * Get user channel code
     *
     * @return string
     */
    public function getUserChannelCode(): string
    {
        return $this->getUserChannel()->getCode();
    }

    /**
     * Get channel choices with user channel code first
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
     *
     * @return CategoryInterface|null
     */
    public function getUserCategoryTree($relatedEntity): ?CategoryInterface
    {
        if (static::USER_PRODUCT_CATEGORY_TYPE === $relatedEntity) {
            return $this->getUserProductCategoryTree();
        }

        return null;
    }

    /**
     * Get user product category tree
     *
     * @return CategoryInterface
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
     *
     * @return string
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
     * Returns the UI user locale
     *
     * @return LocaleInterface|null
     */
    public function getUiLocale()
    {
        return $this->getUserOption('uiLocale');
    }

    /**
     * Returns the UI user locale code
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
     * Get authenticated user
     *
     * @return \Akeneo\UserManagement\Component\Model\UserInterface|null
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

    /**
     * Get the user context as an array
     *
     * @return array
     */
    public function toArray()
    {
        $channels = array_keys($this->getChannelChoicesWithUserChannel());
        $locales = $this->getUserLocaleCodes();

        return [
            'locales'  => $locales,
            'channels' => $channels,
            'locale'   => $this->getUiLocale()->getCode(),
            'channel'  => $this->getUserChannelCode()
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
     * Returns the request locale
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
     * Returns the user session locale
     *
     * @return LocaleInterface|null
     */
    protected function getSessionLocale()
    {
        $request = $this->getCurrentRequest();
        if (null !== $request && $request->hasSession()) {
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
     * Returns the catalog user locale
     *
     * @return LocaleInterface|null
     */
    protected function getUserLocale()
    {
        $locale = $this->getUserOption('catalogLocale');

        return $locale && $this->isLocaleAvailable($locale) ? $locale : null;
    }

    /**
     * Returns the default application locale
     *
     * @return LocaleInterface|null
     */
    protected function getDefaultLocale()
    {
        return $this->localeRepository->findOneByIdentifier($this->defaultLocale);
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
            $user = $token->getUser();
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
        }

        return null;
    }

    /**
     * Get current request
     *
     * @return Request|null
     */
    protected function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}

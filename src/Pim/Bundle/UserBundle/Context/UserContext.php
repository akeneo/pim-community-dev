<?php

namespace Pim\Bundle\UserBundle\Context;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
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

    /** @var ChoicesBuilderInterface */
    protected $choicesBuilder;

    /** @var array */
    protected $userLocales;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param TokenStorageInterface       $tokenStorage
     * @param LocaleRepositoryInterface   $localeRepository
     * @param ChannelRepositoryInterface  $channelRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param RequestStack                $requestStack
     * @param ChoicesBuilderInterface     $choicesBuilder
     * @param string                      $defaultLocale
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        RequestStack $requestStack,
        ChoicesBuilderInterface $choicesBuilder,
        $defaultLocale
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->categoryRepository= $categoryRepository;
        $this->requestStack = $requestStack;
        $this->choicesBuilder = $choicesBuilder;
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
    public function getCurrentLocale()
    {
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

        return $locale;
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
     * @return LocaleInterface[]
     */
    public function getUserLocales()
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
     * @return ChannelInterface
     */
    public function getUserChannel()
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
        $channels = $this->channelRepository->findAll();
        $channelChoices = $this->choicesBuilder->buildChoices($channels);
        $userChannelCode = $this->getUserChannelCode();

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
    public function getUserCategoryTree($relatedEntity)
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
    public function getUserProductCategoryTree()
    {
        $defaultTree = $this->getUserOption('defaultTree');

        return $defaultTree ?: current($this->categoryRepository->getTrees());
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
     * @return UserInterface|null
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
            'locale'   => $this->getUiLocale()->getCode()
        ];
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
     * Get current request
     *
     * @return Request|null
     */
    protected function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}

<?php

namespace Pim\Bundle\UserBundle\Context;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

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

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var LocaleManager */
    protected $localeManager;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var string */
    protected $defaultLocale;

    /** @var Request */
    protected $request;

    /** @var array */
    private $userLocales;

    /**
     * @param SecurityContextInterface $securityContext
     * @param SecurityFacade           $securityFacade
     * @param LocaleManager            $localeManager
     * @param ChannelManager           $channelManager
     * @param string                   $defaultLocale
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        SecurityFacade $securityFacade,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        $defaultLocale
    ) {
        $this->securityContext = $securityContext;
        $this->securityFacade  = $securityFacade;
        $this->localeManager   = $localeManager;
        $this->channelManager  = $channelManager;
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
     * Returns the current locale from the request, or the default locale if no active request is found
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->request ? $this->request->getLocale() : $this->defaultLocale;
    }

    /**
     * Get active locales for which the user has ACLs
     *
     * @return Locale[]
     */
    public function getUserLocales()
    {
        if (!isset($this->userLocales)) {
            $this->userLocales = array();
            foreach ($this->localeManager->getActiveLocales() as $locale) {
                if ($this->securityFacade->isGranted(sprintf('pim_enrich_locale_%s', $locale->getCode()))) {
                    $this->userLocales[] = $locale;
                }
            }
        }

        return $this->userLocales;
    }

    /**
     * Get active locale codes for which the user has ACLs
     *
     * @return array
     */
    public function getUserCodes()
    {
        return array_map(
            function ($locale) {
                return $locale->getCode();
            },
            $this->getUserLocales()
        );
    }

    /**
     * Get user locale code
     *
     * @return Locale|null
     */
    public function getUserLocale()
    {
        $user = $this->getUser();
        if ($user === null) {
            return null;
        }

        $locale = $user->getCatalogLocale();
        if ($locale && $this->securityFacade->isGranted(sprintf('pim_enrich_locale_%s', $locale->getCode()))) {
            return $locale;
        }

        $locales = $this->getUserLocales();

        return array_shift($locales);
    }

    /**
     * Get data locale code
     *
     * @throws \Exception
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    public function getDataLocale()
    {
        if ($dataLocaleCode = $this->request->get(self::REQUEST_LOCALE_PARAM)) {
            foreach ($this->getUserLocales() as $locale) {
                if ($dataLocaleCode === $locale->getCode()) {
                    return $locale;
                }
            }
            throw new \Exception('Data locale must be activated, and accessible through ACLs');
        } else {
            if (null === $dataLocale = $this->getUserLocale()) {
                throw new \Exception('User must have a catalog locale defined and access to this locale in the ACLs');
            }
        }

        return $dataLocale;
    }

    /**
     * Get channel choices with user channel code in first
     *
     * @return string[]
     *
     * @throws \Exception
     */
    public function getChannelChoiceWithUserChannel()
    {
        $channelChoices  = $this->channelManager->getChannelChoices();
        $userChannelCode = $this->getUserChannelCode();
        if (!array_key_exists($userChannelCode, $channelChoices)) {
            throw new \Exception('User channel code is deactivated');
        }

        $userChannelValue = $channelChoices[$userChannelCode];
        $newChannelChoices = array($userChannelCode => $userChannelValue);
        unset($channelChoices[$userChannelCode]);

        return array_merge($newChannelChoices, $channelChoices);
    }

    /**
     * Get user channel
     *
     * @return Channel
     *
     * @throws \Exception
     */
    public function getUserChannel()
    {
        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        $catalogScope = $user->getCatalogScope();

        if (!$catalogScope) {
            throw new \Exception('User must have a catalog scope defined');
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
     * Get current user from security context
     *
     * @return \Oro\Bundle\UserBundle\Entity\User|null
     */
    protected function getUser()
    {
        $token = $this->securityContext->getToken();

        return $token === null ? null : $token->getUser();
    }
}

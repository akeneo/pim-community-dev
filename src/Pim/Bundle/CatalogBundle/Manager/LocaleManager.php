<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;

/**
 * Locale manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleManager
{
    /** @var LocaleRepository */
    protected $repository;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var string */
    protected $defaultLocale;

    /** @var Request */
    protected $request;

    /** @var array */
    private $userLocales;

    /**
     * @param ObjectManager            $objectManager   the storage manager
     * @param SecurityContextInterface $securityContext the security context
     * @param SecurityFacade           $securityFacade  the Security Facade
     * @param string                   $defaultLocale   the default locale for the UI
     */
    public function __construct(
        LocaleRepository $repository,
        SecurityContextInterface $securityContext,
        SecurityFacade $securityFacade,
        $defaultLocale
    ) {
        $this->repository = $repository;
        $this->securityContext = $securityContext;
        $this->securityFacade = $securityFacade;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Sets the current request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
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
     * Get active locales
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getActiveLocales()
    {
        return $this->repository->getActivatedLocales();
    }

    /**
     * Get disabled locales
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getDisabledLocales()
    {
        $criterias = ['activated' => false];

        return $this->getLocales($criterias);
    }

    /**
     * Get locales with criterias
     *
     * @param multitype:string $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getLocales($criterias = [])
    {
        return $this->repository->findBy($criterias);
    }

    /**
     * Get locale by code
     *
     * @param string $code
     *
     * @return Locale
     */
    public function getLocaleByCode($code)
    {
        return $this->repository->findOneBy(['code' => $code]);
    }

    /**
     * Get active codes
     *
     * @return string[]
     */
    public function getActiveCodes()
    {
        $codes = [];
        foreach ($this->getActiveLocales() as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }

    /**
     * Get active locales for which the user has ACLs
     *
     * @return Locale[]
     */
    public function getUserLocales()
    {
        if (!isset($this->userLocales)) {
            $this->userLocales = [];
            foreach ($this->getActiveLocales() as $locale) {
                if ($this->securityFacade->isGranted(sprintf('pim_catalog_locale_%s', $locale->getCode()))) {
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
     * Get the list of available fallback locales
     *
     * @return string[]
     */
    public function getFallbackCodes()
    {
        $locales = $this->repository->getAvailableFallbacks();

        $codes = [];
        foreach ($locales as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }

    /**
     * Get user locale code
     *
     * @return Locale|null
     */
    public function getUserLocale()
    {
        $token = $this->securityContext->getToken();
        if ($token === null || $token->getUser() === null) {
            return null;
        }

        $locale = $token->getUser()->getCatalogLocale();
        if ($locale && $this->securityFacade->isGranted(sprintf('pim_catalog_locale_%s', $locale->getCode()))) {
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
        if ($dataLocaleCode = $this->request->get('dataLocale')) {
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
}

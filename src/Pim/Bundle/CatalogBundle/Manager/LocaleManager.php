<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Acl\ManagerInterface;

/**
 * Locale manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ManagerInterface
     */
    protected $ACLmanager;
    
    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager   the storage manager
     * @param SecurityContextInterface $securityContext the security context
     */
    public function __construct(
        ObjectManager $objectManager,
        SecurityContextInterface $securityContext,
        ManagerInterface $ACLManager,
        $defaultLocale
    ) {
        $this->objectManager = $objectManager;
        $this->securityContext = $securityContext;
        $this->ACLmanager = $ACLManager;
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
     * Get locale repository
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository
     */
    protected function getObjectRepository()
    {
        return $this->objectManager->getRepository('PimCatalogBundle:Locale');
    }

    /**
     * Get active locales
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getActiveLocales()
    {
        return $this->getObjectRepository()->getActivatedLocales();
    }

    /**
     * Get disabled locales
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getDisabledLocales()
    {
        $criterias = array('activated' => false);

        return $this->getLocales($criterias);
    }

    /**
     * Get locales with criterias
     *
     * @param multitype:string $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getLocales($criterias = array())
    {
        return $this->getObjectRepository()->findBy($criterias);
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
        return $this->getObjectRepository()->findOneBy(array('code' => $code));
    }

    /**
     * Get active codes
     *
     * @return multitype:string
     */
    public function getActiveCodes()
    {
        $codes = array();
        foreach ($this->getActiveLocales() as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }

    /**
     * Get active locales for which the user has ACLs
     * 
     * @return array
     */
    public function getUserLocales()
    {
        $locales = array();
        foreach ($this->getActiveLocales() as $code => $locale) {
            if ($this->ACLmanager->isResourceGranted(sprintf('pim_catalog_locale_%s', $locale->getCode()))) {
                $locales[] = $locale;
            }
        }

        return $locales;
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
     * @return array
     */
    public function getFallbackCodes()
    {
        $locales = $this->getObjectRepository()->getAvailableFallbacks();

        $codes = array();
        foreach ($locales as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }

    /**
     * Get active codes with user locale code in first
     *
     * @return multitype:string
     */
    public function getActiveCodesWithUserLocale()
    {
        $localeCodes = $this->getActiveCodes();
        $userLocaleCode = $this->getUserLocale()->getCode();

        unset($localeCodes[array_find($userLocaleCode, $localeCodes)]);
        array_unshift($localeCodes, $userLocaleCode);

        return $localeCodes;
    }

    /**
     * Get user locale code
     *
     * @return string
     */
    public function getUserLocale()
    {
        if ($this->securityContext->getToken() === null) {
            return null;
        }

        if ($this->securityContext->getToken()->getUser() === null) {
            return null;
        }
        $user = $this->securityContext->getToken()->getUser();

        $localeCode = (string) $user->getValue('cataloglocale');
        $userLocales = $this->getUserLocales();

        foreach ($userLocales as $locale) {
            if ($localeCode == $locale->getCode()) {
                $userLocale = $locale;
                break;
            }
        }
        if (!isset($userLocale)) {
            $userLocale = array_shift($userLocales);
        }

        return $userLocale;
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
        $dataLocaleCode = $this->request->get('dataLocale');
        if ($dataLocaleCode) {
            foreach ($this->getUserLocales() as $locale) {
                if ($dataLocaleCode == $locale->getCode()) {
                    $dataLocale = $locale;
                    break;
                }
            }
            if (!isset($dataLocale)) {
                throw new \Exception('Data locale must be activated, and accessible through ACLs');
            }
        } else {
            $dataLocale = $this->getUserLocale();
            if (!$dataLocale) {
                throw new \Exception('User must have a catalog locale defined and access to this locale in the ACLs');
            }
        }

        return $dataLocale;
    }
}

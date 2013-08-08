<?php

namespace Pim\Bundle\ConfigBundle\Manager;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Locale manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
     * Constructor
     *
     * @param ObjectManager            $objectManager   the storage manager
     * @param SecurityContextInterface $securityContext the security context
     */
    public function __construct(ObjectManager $objectManager, SecurityContextInterface $securityContext)
    {
        $this->objectManager = $objectManager;
        $this->securityContext = $securityContext;
    }

    /**
     * Get locale repository
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Repository\LocaleRepository
     */
    protected function getObjectRepository()
    {
        return $this->objectManager->getRepository('PimConfigBundle:Locale');
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
     * Get active codes with user locale code in first
     *
     * @return multitype:string
     */
    public function getActiveCodesWithUserLocale()
    {
        $localeCodes = $this->getActiveCodes();
        $userLocaleCode = $this->getUserLocaleCode();

        unset($localeCodes[$userLocaleCode]);
        array_unshift($localeCodes, $userLocaleCode);

        return $localeCodes;
    }

    /**
     * Get user locale code
     *
     * @return string
     */
    public function getUserLocaleCode()
    {
        $user = $this->securityContext->getToken()->getUser();

        return (string) $user->getValue('cataloglocale');
    }
}

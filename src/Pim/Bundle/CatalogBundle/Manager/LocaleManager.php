<?php

namespace Pim\Bundle\CatalogBundle\Manager;

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

    /**
     * @param LocaleRepository $repository
     */
    public function __construct(LocaleRepository $repository)
    {
        $this->repository = $repository;
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
        return $this->repository->findOneBy(array('code' => $code));
    }

    /**
     * Get active codes
     *
     * @return string[]
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
     * Get the list of available fallback locales
     *
     * @return string[]
     */
    public function getFallbackCodes()
    {
        $locales = $this->repository->getAvailableFallbacks();

        $codes = array();
        foreach ($locales as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }
}

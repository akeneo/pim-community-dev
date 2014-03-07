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
     * @return Locale[]
     */
    public function getActiveLocales()
    {
        return $this->repository->getActivatedLocales();
    }

    /**
     * Get disabled locales
     *
     * @return Locale[]
     */
    public function getDisabledLocales()
    {
        $criterias = array('activated' => false);

        return $this->getLocales($criterias);
    }

    /**
     * Get locales with criterias
     *
     * @param array $criterias
     *
     * @return Locale[]
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
        return array_map(
            function ($locale) {
                return $locale->getCode();
            },
            $this->getActiveLocales()
        );
    }
}

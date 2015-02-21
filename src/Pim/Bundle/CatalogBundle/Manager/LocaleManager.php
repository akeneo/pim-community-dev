<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Locale manager
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleManager
{
    /** @var LocaleRepositoryInterface */
    protected $repository;

    /**
     * @param LocaleRepositoryInterface $repository
     */
    public function __construct(LocaleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get active locales
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale[]
     */
    public function getActiveLocales()
    {
        return $this->repository->getActivatedLocales();
    }

    /**
     * Get disabled locales
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale[]
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale[]
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
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

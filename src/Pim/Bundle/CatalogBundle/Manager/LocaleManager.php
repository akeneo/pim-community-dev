<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Locale manager
 *
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
     * @return LocaleInterface[]
     */
    public function getActiveLocales()
    {
        return $this->repository->getActivatedLocales();
    }

    /**
     * Get disabled locales
     *
     * @return LocaleInterface[]
     */
    public function getDisabledLocales()
    {
        $criterias = ['activated' => false];

        return $this->getLocales($criterias);
    }

    /**
     * Get locales with criterias
     *
     * @param array $criterias
     *
     * @return LocaleInterface[]
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
     * @return LocaleInterface
     */
    public function getLocaleByCode($code)
    {
        return $this->repository->findOneByIdentifier($code);
    }

    /**
     * Get active codes
     *
     * @return string[]
     */
    public function getActiveCodes()
    {
        return array_map(
            function (LocaleInterface $locale) {
                return $locale->getCode();
            },
            $this->getActiveLocales()
        );
    }

    /**
     * Check if a locale is activated
     *
     * @param LocaleInterface[] $locales
     * @param string            $localeCode
     *
     * @return bool
     */
    public function isActivatedLocale(array $locales, $localeCode)
    {
        $foundLocale = null;

        $iterator = new \ArrayIterator($locales);

        while ($iterator->valid() && null === $foundLocale) {
            $locale = $iterator->current();
            if ($locale->getCode() === $localeCode) {
                $foundLocale = $locale;
            }
            $iterator->next();
        }

        if (null === $foundLocale) {
            throw new \OutOfBoundsException(sprintf('locale code %s is unknown', $localeCode));
        }

        return $foundLocale->isActivated();
    }
}

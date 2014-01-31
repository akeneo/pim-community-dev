<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;

/**
 * Currency manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyManager
{
    /**
     * @var CurrencyRepository $repository
     */
    protected $repository;

    /**
     * Constructor
     * @param CurrencyRepository $repository
     */
    public function __construct(CurrencyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get active currencies
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getActiveCurrencies()
    {
        $criterias = array('activated' => true);

        return $this->getCurrencies($criterias);
    }

    /**
     * Get currencies with criterias
     *
     * @param array $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getCurrencies($criterias = array())
    {
        return $this->repository->findBy($criterias);
    }

    /**
     * Get active codes
     *
     * @return string[]
     */
    public function getActiveCodes()
    {
        $codes = array();
        foreach ($this->getActiveCurrencies() as $currency) {
            $codes[] = $currency->getCode();
        }

        return $codes;
    }

    /**
     * Get active code choices
     *
     * Prior to PHP 5.4 array_combine() does not accept
     * empty array as argument.
     *
     * @see http://php.net/array_combine#refsect1-function.array-combine-changelog
     *
     * @return array
     */
    public function getActiveCodeChoices()
    {
        $codes = $this->getActiveCodes();

        if (empty($codes)) {
            return array();
        }

        return array_combine($codes, $codes);
    }
}

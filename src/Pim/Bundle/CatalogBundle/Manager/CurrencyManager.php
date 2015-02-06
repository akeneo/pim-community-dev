<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;

/**
 * Currency manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyManager
{
    /** @var CurrencyRepositoryInterface $repository */
    protected $repository;

    /**
     * Constructor
     *
     * @param CurrencyRepositoryInterface $repository
     */
    public function __construct(CurrencyRepositoryInterface $repository)
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
        return $this->repository->getActivatedCurrencyCodes();
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;

/**
 * Currency manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
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
     * Get active codes
     *
     * @deprecated CurrencyRepositoryInterface::getActivatedCurrencyCodes must be used
     *
     * @return string[]
     */
    public function getActiveCodes()
    {
        return $this->repository->getActivatedCurrencyCodes();
    }
}

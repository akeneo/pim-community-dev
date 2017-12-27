<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectsRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\CurrencyInterface;

/**
 * Currency repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CurrencyRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository, IdentifiableObjectsRepositoryInterface
{
    /**
     * Return an array of activated currencies
     *
     * @return CurrencyInterface[]
     */
    public function getActivatedCurrencies();

    /**
     * Return an array of currency codes
     *
     * @return array
     */
    public function getActivatedCurrencyCodes();

}

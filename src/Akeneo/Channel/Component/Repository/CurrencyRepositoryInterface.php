<?php

namespace Akeneo\Channel\Component\Repository;

use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Currency repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CurrencyRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
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

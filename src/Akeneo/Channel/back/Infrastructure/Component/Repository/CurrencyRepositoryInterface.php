<?php

namespace Akeneo\Channel\Infrastructure\Component\Repository;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Persistence\ObjectRepository;

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
     * @return array<string>
     */
    public function getActivatedCurrencyCodes();
}

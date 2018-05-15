<?php

namespace Akeneo\Channel\Component\Repository;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Channel repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChannelRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Return the number of existing channels
     *
     * @return int
     */
    public function countAll();

    /**
     * Return an array of channel codes
     *
     * @return array
     */
    public function getChannelCodes();

    /**
     * Get full channels with locales and currencies
     *
     * @return ChannelInterface[]
     */
    public function getFullChannels();

    /**
     * Get channels count for the given currency
     *
     * @param CurrencyInterface $currency
     *
     * @return int
     */
    public function getChannelCountUsingCurrency(CurrencyInterface $currency);

    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @param string $localeCode
     *
     * @return string[]
     */
    public function getLabelsIndexedByCode($localeCode);
}

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
     */
    public function countAll(): int;

    /**
     * Return an array of channel codes
     */
    public function getChannelCodes(): array;

    /**
     * Get full channels with locales and currencies
     *
     * @return ChannelInterface[]
     */
    public function getFullChannels(): array;

    /**
     * Get channels count for the given currency
     *
     * @param CurrencyInterface $currency
     */
    public function getChannelCountUsingCurrency(CurrencyInterface $currency): int;

    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @param string $localeCode
     *
     * @return string[]
     */
    public function getLabelsIndexedByCode(string $localeCode): array;
}

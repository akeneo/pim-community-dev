<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;

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
     * TODO: should be extracted in an enrich bundle repository
     *
     * @return mixed a query builder
     */
    public function createDatagridQueryBuilder();

    /**
     * Get the deleted locales of a channel (the channel is updated but not flushed yet).
     *
     * @param ChannelInterface $channel
     *
     * @deprecated will be removed in 1.7, has been used by the removed method CompletenessManager::scheduleForChannel
     *
     * @return array the list of deleted locales
     */
    public function getDeletedLocaleIdsForChannel(ChannelInterface $channel);

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
     * @return string[]
     */
    public function getLabelsIndexedByCode();
}

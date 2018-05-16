<?php

namespace Akeneo\Channel\Component\Repository;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Locale repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocaleRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Return an array of activated locales
     *
     * @return LocaleInterface[]
     */
    public function getActivatedLocales();

    /**
     * Return an array of activated locales codes
     *
     * @return array
     */
    public function getActivatedLocaleCodes();

    /**
     * Return a query builder for activated locales
     *
     * @return mixed
     */
    public function getActivatedLocalesQB();

    /**
     * Get the deleted locales of a channel (the channel is updated but not flushed yet).
     *
     * @param ChannelInterface $channel
     *
     * @return array the list of deleted locales
     */
    public function getDeletedLocalesForChannel(ChannelInterface $channel);

    /**
     * Return the number of activated locales
     *
     * @return int
     */
    public function countAllActivated();
}

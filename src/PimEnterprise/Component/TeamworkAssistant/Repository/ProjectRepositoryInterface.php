<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Repository;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectRepositoryInterface extends
    ObjectRepository,
    IdentifiableObjectRepositoryInterface,
    SearchableRepositoryInterface,
    CursorableRepositoryInterface
{
    /**
     * @param LocaleInterface $locale
     *
     * @return CursorInterface
     */
    public function findByLocale(LocaleInterface $locale);

    /**
     * @param ChannelInterface $channel
     *
     * @return CursorInterface
     */
    public function findByChannel(ChannelInterface $channel);
}

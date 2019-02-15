<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Query;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface DeleteVariationsForChannelId
{
    /**
     * Deletes all the asset variations related to a channel.
     *
     * @param int $channelId
     */
    public function execute(int $channelId): void;
}

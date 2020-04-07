<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Query;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
interface ChannelExistsAndBoundToLocaleInterface
{
    public function doesChannelExist(string $channelCode): bool;

    /**
     * A locale is active when is bound to at least one channel.
     * And not active when bound to none channel.
     *
     * @param string $localeCode
     * @return bool
     */
    public function isLocaleActive(string $localeCode): bool;

    public function isLocaleBoundToChannel(string $localeCode, string $channelCode): bool;
}

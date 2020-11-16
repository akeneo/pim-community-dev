<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Query\PublicApi;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChannelExistsWithLocaleInterface
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

    public function clearCache();
}

<?php

namespace Akeneo\Channel\Infrastructure\Component\Query\PublicApi;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCaseSensitiveChannelCodeInterface
{

    public function forChannelCode(string $channelCode): string;
}

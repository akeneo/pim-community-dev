<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCaseSensitiveChannelCodeInterface
{
    /**
     * Returns the case sensitive channel code from any channel code
     * Example: forChannelCode('ECommeRce') => 'ecommerce'.
     */
    public function forChannelCode(string $channelCode): string;
}

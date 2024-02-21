<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Messenger\Config;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
enum TransportType: string
{
    case DOCTRINE = 'DOCTRINE';
    case IN_MEMORY = 'IN_MEMORY';
    case PUB_SUB = 'PUB_SUB';
    case SYNC = 'SYNC';
}

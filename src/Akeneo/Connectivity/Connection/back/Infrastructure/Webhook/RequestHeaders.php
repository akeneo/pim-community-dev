<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestHeaders
{
    public const HEADER_REQUEST_SIGNATURE = 'X-Akeneo-Request-Signature';
    public const HEADER_REQUEST_TIMESTAMP = 'X-Akeneo-Request-Timestamp';
    public const HEADER_REQUEST_USERAGENT = 'User-Agent';
}

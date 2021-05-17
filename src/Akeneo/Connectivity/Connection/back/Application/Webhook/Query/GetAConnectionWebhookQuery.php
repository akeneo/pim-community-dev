<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Query;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAConnectionWebhookQuery
{
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function code(): string
    {
        return $this->code;
    }
}

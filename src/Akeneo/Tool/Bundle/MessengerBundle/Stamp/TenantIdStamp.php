<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TenantIdStamp implements StampInterface
{
    public function __construct(private string $pimTenantId)
    {
    }

    public function pimTenantId(): string
    {
        return $this->pimTenantId;
    }
}

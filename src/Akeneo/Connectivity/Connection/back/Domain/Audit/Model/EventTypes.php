<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class EventTypes
{
    public const PRODUCT_CREATED = 'product_created';
    public const PRODUCT_UPDATED = 'product_updated';
    public const PRODUCT_READ = 'product_read';
}

<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CacheClearer implements CacheClearerInterface
{
    public function clear(): void
    {
    }
}

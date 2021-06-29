<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CacheClearerInterface
{
    public function clear(): void;
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

use Symfony\Contracts\Service\ResetInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WarmupAwareQueryInterface extends ResetInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function warmup(array $options = []): void;
}

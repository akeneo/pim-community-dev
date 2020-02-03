<?php

declare(strict_types=1);

namespace Akeneo\Platform;

/**
 * Interface VersionProviderInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionProviderInterface
{
    public function getEdition(): string;

    public function getPatch(): string;

    public function getFullVersion(): string;

    public function isSaaSVersion(): bool;
}

<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\PimVersionBundle;

/**
 * Interface VersionProviderInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionProviderInterface
{
    /**
     * Return the edition. The value is a contract as it's used outside the PIM for the PIM tracker, to get announcements, etc.
     * Therefore, do not change it without extreme care.
     *  - CE
     *  - Serenity
     *  - Growth Edition
     *  - Free Trial Edition
     *
     * @return string
     */
    public function getEdition(): string;

    public function getVersion(): string;

    public function getPatch(): string;

    public function getMinorVersion(): string;

    public function getFullVersion(): string;

    public function isSaaSVersion(): bool;
}

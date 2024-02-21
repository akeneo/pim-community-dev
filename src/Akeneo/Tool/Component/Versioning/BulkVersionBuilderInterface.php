<?php

namespace Akeneo\Tool\Component\Versioning;

use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;

/**
 * Builds versions for a bulk of versionable objects.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface BulkVersionBuilderInterface
{
    /**
     * Build versions from the specified versionables
     *
     * @param VersionableInterface[] $versionables
     *
     * @return VersionInterface[]
     */
    public function buildVersions(array $versionables);
}

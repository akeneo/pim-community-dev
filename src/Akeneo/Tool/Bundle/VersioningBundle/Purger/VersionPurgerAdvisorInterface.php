<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

/**
 * Checks if a version should be purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionPurgerAdvisorInterface
{
    /**
     * Checks if the advisor supports the version
     *
     * @param PurgeableVersionList $versionList
     *
     * @return bool
     */
    public function supports(PurgeableVersionList $versionList);

    /**
     * Indicates the versions that needs to be purged
     *
     * @param PurgeableVersionList $versionList
     *
     * @return PurgeableVersionList
     */
    public function isPurgeable(PurgeableVersionList $versionList): PurgeableVersionList;
}

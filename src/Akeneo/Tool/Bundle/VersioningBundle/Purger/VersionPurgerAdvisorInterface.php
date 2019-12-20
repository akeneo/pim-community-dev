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
     * @param PurgeableVersion $version
     *
     * @return bool
     */
    public function supports(PurgeableVersion $version);

    /**
     * Indicates if the version needs to be purged
     *
     * @param PurgeableVersion $version
     * @param array            $options
     *
     * @return bool
     */
    public function isPurgeable(PurgeableVersion $version, array $options);
}

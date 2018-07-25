<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Component\Versioning\Model\VersionInterface;

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
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function supports(VersionInterface $version);

    /**
     * Indicates if the version needs to be purged
     *
     * @param VersionInterface $version
     * @param array $options
     *
     * @return bool
     */
    public function isPurgeable(VersionInterface $version, array $options);
}

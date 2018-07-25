<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

/**
 * Purge versions according to registered advisors
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionPurgerInterface
{
    /**
     * Purge the versions
     *
     * @param array $options
     */
    public function purge(array $options);

    /**
     * Returns the number of versions impacted by the purge configuration
     *
     * @param array $options
     *
     * @return int
     */
    public function getVersionsToPurgeCount(array $options);

    /**
     * Registers an advisor into the purger
     *
     * @param VersionPurgerAdvisorInterface $versionPurgerAdvisor
     */
    public function addVersionPurgerAdvisor(VersionPurgerAdvisorInterface $versionPurgerAdvisor);
}

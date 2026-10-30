<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\PimVersionBundle\Version;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PimVersion
{
    public function versionCodename(): string;

    /** Edition name is used inside/outside the PIM for many things such as:
     *  - to collect data in the tracker (outside of the PIM)
     *  - to get last updates from updates.akeneo.com
     *  - to get news in the communication channel
     *
     * The edition name ("CE" for Community Edition) differs from the edition code ("community_edition_instance").
     *
     * Please do not change it or remove without extreme care. This name is a contract used by tools above.
     * Changing would break the tools or have an impact on PIM tracker data.
     */
    public function editionName(): string;

    /**
     * Version can change between different requests in Saas. Therefore, the version is not hardcoded in an artifact.
     * This method returns if the current version corresponds to the edition code of the current request context.

     */
    public function isEditionCode(string $editionCode): bool;
}

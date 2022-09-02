<?php

namespace Akeneo\Platform\Bundle\PimVersionBundle\Version;

/**
 * PIM Version
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CommunityVersion implements PimVersion
{
    /** @staticvar string */
    private const VERSION_CODENAME = 'Community master';

    /** @staticvar string */
    private const EDITION_NAME = 'CE';

    /** @staticvar string **/
    private const EDITION_CODE = 'community_edition_instance';

    public function versionCodename(): string
    {
        return self::VERSION_CODENAME;
    }

    public function editionName(): string
    {
        return self::EDITION_NAME;
    }

    public function isSaas(): bool
    {
        return false;
    }

    public function isEditionCode(string $editionCode): bool
    {
        return $editionCode === self::EDITION_CODE;
    }
}

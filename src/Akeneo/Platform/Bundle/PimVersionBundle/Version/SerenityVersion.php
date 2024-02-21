<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\PimVersionBundle\Version;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SerenityVersion implements PimVersion
{
    /** @staticvar string */
    private const VERSION_CODENAME = 'Buttercup';

    /** @staticvar string */
    private const EDITION_NAME = 'Serenity';

    /** @staticvar string **/
    private const EDITION_CODE = 'serenity_instance';

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
        return true;
    }

    public function isEditionCode(string $editionCode): bool
    {
        return $editionCode === self::EDITION_CODE;
    }
}

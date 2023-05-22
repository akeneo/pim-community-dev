<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter;

use Akeneo\Platform\Installer\Domain\Service\UserConfigurationResetterInterface;
use Doctrine\DBAL\Connection;

class UserUiLocaleResetter implements UserConfigurationResetterInterface
{
    private const DEFAULT_UI_LOCALE_CODE = 'en_US';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(): void
    {
        $this->connection->executeStatement(<<<SQL
            UPDATE oro_user SET ui_locale_id = (
                SELECT id
                FROM pim_catalog_locale 
                WHERE code = :defaultUiLocaleCode
            ) 
            WHERE ui_locale_id NOT IN (
                SELECT id
                FROM pim_catalog_locale
            )
        SQL, [
            'defaultUiLocaleCode' => self::DEFAULT_UI_LOCALE_CODE,
        ]);
    }
}

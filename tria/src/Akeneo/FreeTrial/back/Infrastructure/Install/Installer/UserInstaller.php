<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Doctrine\DBAL\Connection;

final class UserInstaller implements FixtureInstaller
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function install(): void
    {
        // Deactivate the default admin user
        $query = <<<SQL
UPDATE oro_user
    SET enabled = 0, user_type = 'api'
    WHERE username = 'admin';
SQL;

        $this->dbConnection->executeQuery($query);
    }
}

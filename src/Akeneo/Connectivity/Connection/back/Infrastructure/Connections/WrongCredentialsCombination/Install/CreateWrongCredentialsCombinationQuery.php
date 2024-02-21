<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Install;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateWrongCredentialsCombinationQuery
{
    public const QUERY = <<<SQL
CREATE TABLE akeneo_connectivity_connection_wrong_credentials_combination
(
    connection_code     varchar(100) not null,
    username            varchar(100) not null,
    authentication_date datetime     not null,
    PRIMARY KEY (connection_code, username)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC

SQL;
}

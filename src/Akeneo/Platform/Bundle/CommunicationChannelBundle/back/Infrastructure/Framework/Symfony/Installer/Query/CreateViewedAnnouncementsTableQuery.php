<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer\Query;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateViewedAnnouncementsTableQuery
{
    const QUERY = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_communication_channel_viewed_announcements(
    announcement_id VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (announcement_id, user_id),
    CONSTRAINT FK_COMMUNICATION_CHANNEL_VIEWED_ANNOUNCEMENTS_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id) ON DELETE CASCADE,
    UNIQUE INDEX IDX_VIEWED_ANNOUNCEMENTS_user_id_announcement_id (user_id, announcement_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}

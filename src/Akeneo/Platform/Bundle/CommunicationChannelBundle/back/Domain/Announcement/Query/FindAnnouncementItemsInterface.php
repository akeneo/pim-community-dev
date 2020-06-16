<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindAnnouncementItemsInterface
{
    /**
     * @return AnnouncementItem[]
     */
    public function byPimVersion(string $pimEdition, string $pimVersion): array;
}

<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\VersionProviderInterface;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ListAnnouncementsHandler
{
    /** @var FindAnnouncementItemsInterface */
    private  $findAnnouncementItems;

    public function __construct(
        VersionProviderInterface $versionProvider,
        FindAnnouncementItemsInterface $findAnnouncementItems
    ) {
        $this->versionProvider = $versionProvider;
        $this->findAnnouncementItems = $findAnnouncementItems;
    }

    public function execute(): array
    {
        $edition = $this->versionProvider->getEdition();
        $version = $this->versionProvider->getPatch();

        $announcementItems = $this->findAnnouncementItems->byPimVersion($edition, $version);

        return $announcementItems;
    }
}

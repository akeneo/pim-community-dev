<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Application\Announcement\Command;

use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Model\Write\ViewedAnnouncement;
use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Repository\ViewedAnnouncementRepositoryInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AddViewedAnnouncementsByUserHandler
{
    /** @var ViewedAnnouncementRepositoryInterface */
    private $viewedAnnouncementRepository;

    public function __construct(ViewedAnnouncementRepositoryInterface $viewedAnnouncementRepository)
    {
        $this->viewedAnnouncementRepository = $viewedAnnouncementRepository;
    }

    public function execute(AddViewedAnnouncementsByUserCommand $command): void
    {
        $viewedAnnouncements = array_map(function ($viewedAnnouncementId) use ($command) {
            return ViewedAnnouncement::create(
                $viewedAnnouncementId,
                $command->userId()
            );
        }, $command->viewedAnnouncementIds());

        $this->viewedAnnouncementRepository->create($viewedAnnouncements);
    }
}

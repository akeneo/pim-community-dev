<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Write;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ViewedAnnouncement
{
    /** @var string */
    private $announcementId;

    /** @var int */
    private $userId;

    private function __construct(string $announcementId, int $userId)
    {
        $this->announcementId = $announcementId;
        $this->userId = $userId;
    }

    public static function create(string $announcementId, int $userId): self
    {
        return new self($announcementId, $userId);
    }

    public function announcementId(): string
    {
        return $this->announcementId;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}

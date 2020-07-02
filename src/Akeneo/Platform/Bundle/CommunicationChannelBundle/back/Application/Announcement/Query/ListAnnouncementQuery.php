<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ListAnnouncementQuery
{
    /** @var string */
    private $edition;

    /** @var string */
    private $version;

    /** @var int */
    private $userId;

    /** @var string|null */
    private $searchAfter;

    /** @var int */
    private $limit;

    public function __construct(string $edition, string $version, int $userId, ?string $searchAfter, int $limit)
    {
        $this->edition = $edition;
        $this->version = $version;
        $this->userId = $userId;
        $this->searchAfter = $searchAfter;
        $this->limit = $limit;
    }

    public function edition(): string
    {
        return $this->edition;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function searchAfter(): ?string
    {
        return $this->searchAfter;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}

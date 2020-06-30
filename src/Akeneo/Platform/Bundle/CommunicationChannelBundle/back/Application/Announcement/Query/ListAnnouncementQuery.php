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
    /** @var string|null */
    private $searchAfter;

    /** @var int */
    private $limit;

    public function __construct(?string $searchAfter, int $limit)
    {
        $this->searchAfter = $searchAfter;
        $this->limit = $limit;
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

<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

use DateTimeImmutable;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AnnouncementItem
{
    private const DATE_FORMAT = 'F\, jS Y';
    private const DATE_INTERVAL_FORMAT = 'P%sD';

    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var string|null */
    private $img;

    /** @var string|null */
    private $altImg;

    /** @var string */
    private $link;

    /** @var \DateTimeImmutable */
    private $startDate;

    /** @var int */
    private $notificationDuration;

    /** @var string[] */
    private $tags;

    /**
     * @param string $id
     * @param string $title
     * @param string $description
     * @param null|string $img
     * @param null|string $altImg
     * @param string $link
     * @param DateTimeImmutable $startDate
     * @param int $notificationDuration
     * @param string[] $tags
     * @return void
     */
    public function __construct(
        string $id,
        string $title,
        string $description,
        ?string $img,
        ?string $altImg,
        string $link,
        \DateTimeImmutable $startDate,
        int $notificationDuration,
        array $tags
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->img = $img;
        $this->altImg = $altImg;
        $this->link = $link;
        $this->startDate = $startDate;
        $this->notificationDuration = $notificationDuration;
        $this->tags = $tags;
    }

    /**
     * @return array<string, array<string>|int|string|null>
     */
    public function toArray(): array
    {
        $this->addNewTag();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'img' => $this->img,
            'altImg' => $this->altImg,
            'link' => $this->link,
            'startDate' => $this->startDate->format(self::DATE_FORMAT),
            'tags' => $this->tags,
        ];
    }

    /**
     * @return void
     */
    private function addNewTag(): void
    {
        $currentDate = new \DateTimeImmutable();
        $dateInterval = new \DateInterval(sprintf(self::DATE_INTERVAL_FORMAT, $this->notificationDuration));
        $endDate = $this->startDate->add($dateInterval);

        if ($currentDate > $this->startDate && $currentDate < $endDate) {
            array_unshift($this->tags, 'new');
        }
    }
}

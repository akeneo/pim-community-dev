<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

use DateTimeImmutable;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AnnouncementItem
{
    private const DATE_FORMAT = 'F\, jS Y';
    private const DATE_INTERVAL_FORMAT = 'P%sD';

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var string|null */
    private $image;

    /** @var string|null */
    private $altImage;

    /** @var string */
    private $link;

    /** @var \DateTimeImmutable */
    private $startDate;

    /** @var int */
    private $notificationDuration;

    /** @var string[] */
    private $tags;

    /** @var string[] */
    private $editions;

    /**
     * @param string $title
     * @param string $description
     * @param null|string $image
     * @param null|string $altImage
     * @param string $link
     * @param DateTimeImmutable $startDate
     * @param int $notificationDuration
     * @param string[] $tags
     * @param string[] $editions
     * @return void
     */
    public function __construct(
        string $title,
        string $description,
        ?string $image,
        ?string $altImage,
        string $link,
        \DateTimeImmutable $startDate,
        int $notificationDuration,
        array $tags,
        array $editions
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->altImage = $altImage;
        $this->link = $link;
        $this->startDate = $startDate;
        $this->notificationDuration = $notificationDuration;
        $this->tags = $tags;
        $this->editions = $editions;
    }

    /**
     * @return array<string, array<string>|int|string|null>
     */
    public function toArray(): array
    {
        $this->addNewTag();

        return [
            'title' => $this->title,
            'description' => $this->description,
            'img' => $this->image,
            'altImg' => $this->altImage,
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

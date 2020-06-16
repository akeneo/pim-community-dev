<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AnnouncementItem
{
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

    /** @var \DateTimeImmutable */
    private $endDate;

    /** @var int */
    private $notificationDuration;

    /** @var array */
    private $tags;

    /** @var array */
    private $editions;

    public function __construct(
        string $title,
        string $description,
        ?string $img,
        ?string $altImg,
        string $link,
        \DateTimeImmutable $startDate,
        int $notificationDuration,
        array $tags,
        array $editions
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->img = $img;
        $this->altImg = $altImg;
        $this->link = $link;
        $this->startDate = $startDate;
        $this->notificationDuration = $notificationDuration;
        $this->tags = $tags;
        $this->editions = $editions;
    }

    private function addNewTag(): array
    {
        $currentDate = new \DateTimeImmutable();
        $dateInterval = new \DateInterval('P' . $this->notificationDuration . 'D');
        $endDate = $this->startDate->add($dateInterval);

        if ($currentDate > $this->startDate && $currentDate < $endDate) {
            array_unshift($this->tags, 'new');
        }

        return $this->tags;
    }

    private function formatDate(\DateTimeImmutable $date): string
    {
        return $date->format('F\, jS Y');
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'img' => $this->img,
            'altImg' => $this->altImg,
            'link' => $this->link,
            'startDate' => $this->formatDate($this->startDate),
            'notificationDuration' => $this->notificationDuration,
            'tags' => $this->addNewTag($this->tags),
            'editions' => $this->editions,
        ];
    }
}

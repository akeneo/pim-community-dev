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

    public function normalize()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'img' => $this->img,
            'altImg' => $this->altImg,
            'link' => $this->link,
            'startDate' => $this->startDate,
            'notificationDuration' => $this->notificationDuration,
            'tags' => $this->tags,
            'editions' => $this->editions,
        ];
    }
}

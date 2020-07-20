<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AnnouncementItem
{
    private const DATE_FORMAT = 'F\, jS Y';

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

    /** @var \DateTimeImmutable */
    private $endDate;

    /** @var string[] */
    private $tags;

    public function __construct(
        string $id,
        string $title,
        string $description,
        ?string $img,
        ?string $altImg,
        string $link,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $tags
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->img = $img;
        $this->altImg = $altImg;
        $this->link = $link;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tags = $tags;
    }

    /**
     * @return array<string, array<string>|int|string|null>
     */
    public function toArray(): array
    {
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

    public function shouldBeNotified(array $viewedAnnouncementIds): bool
    {
        $currentDate = new \DateTimeImmutable();

        return $this->startDate <= $currentDate && $currentDate <= $this->endDate && !in_array($this->id, $viewedAnnouncementIds);
    }

    public function toNotify(): self
    {
        $tags = $this->tags;
        $tags[] = 'new';

        return new self(
            $this->id,
            $this->title,
            $this->description,
            $this->img,
            $this->altImg,
            $this->link,
            $this->startDate,
            $this->endDate,
            $tags,
        );
    }
}

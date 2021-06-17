<?php

namespace Akeneo\AssetManager\Common\Fake;

class InMemoryDateRepository
{
    /** @var \DateTime **/
    private ?\DateTime $date = null;

    public function getCurrentDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|\DateTimeImmutable $date
     */
    public function setCurrentDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }
}

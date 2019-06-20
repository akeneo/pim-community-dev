<?php

namespace Akeneo\ReferenceEntity\Common\Fake;

class InMemoryDateRepository
{
    /** @var \DateTime **/
    private $date;

    public function getCurrentDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setCurrentDate(\DateTime $date): void
    {
        $this->date = $date;
    }
}

<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures;

use Oro\Bundle\EmailBundle\Entity\EmailAddress as OriginalEmailAddress;

class EmailAddress extends OriginalEmailAddress
{
    public function __construct($date = null)
    {
        $this->created = $date;
        $this->updated = $date;
    }
}

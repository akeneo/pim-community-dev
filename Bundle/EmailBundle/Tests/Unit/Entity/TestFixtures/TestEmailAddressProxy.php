<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures;

use Oro\Bundle\EmailBundle\Entity\EmailAddress as OriginalEmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;

class TestEmailAddressProxy extends OriginalEmailAddress
{
    /**
     * @var EmailOwnerInterface
     */
    private $owner;

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(EmailOwnerInterface $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }
}

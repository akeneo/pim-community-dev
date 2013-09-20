<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailOrigin;

class TestEmailOrigin extends EmailOrigin
{
    public function __construct($id = null)
    {
        parent::__construct();
        $this->id = $id;
    }
}

<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures;

class Imap1 extends \Oro\Bundle\ImapBundle\Mail\Storage\Imap
{
    public function __construct($params)
    {
    }

    public function __destruct()
    {
    }

    public function capability()
    {
        return array('FEATURE1');
    }
}

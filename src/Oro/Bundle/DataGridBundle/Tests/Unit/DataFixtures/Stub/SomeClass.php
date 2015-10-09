<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub;

class SomeClass
{
    const TEST = 42;

    public function getAnswerToLifeAndEverything()
    {
        return self::TEST;
    }

    public static function testStaticCall()
    {
        return self::TEST * 2;
    }
}

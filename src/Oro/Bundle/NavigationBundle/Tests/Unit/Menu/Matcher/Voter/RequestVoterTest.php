<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu\Matcher\Voter;

use Oro\Bundle\NavigationBundle\Menu\Matcher\Voter;
use Symfony\Component\HttpFoundation\Request;

class RequestVoterTest extends \PHPUnit_Framework_TestCase
{

    public function testUriVoterConstruct()
    {
        $uri = 'test.uri';

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->once())
            ->method('getRequestUri')
            ->will($this->returnValue($uri));

        $itemMock = $this->getMock('Knp\Menu\ItemInterface');
        $itemMock->expects($this->exactly(2))
            ->method('getUri')
            ->will($this->returnValue($uri));

        $voter = new Voter\RequestVoter();
        $voter->setRequest($request);

        $this->assertTrue($voter->matchItem($itemMock));
    }
}

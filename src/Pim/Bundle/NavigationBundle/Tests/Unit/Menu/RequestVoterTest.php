<?php

namespace Pim\Bundle\NavigationBundle\Tests\Unit\Menu;

use Pim\Bundle\NavigationBundle\Menu\RequestVoter;

/**
 * Test
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->request = $this->getRequestMock();
        $this->target = new RequestVoter(
            $this->getContainerMock($this->request)
        );
    }

    /**
     * Test related method
     */
    public function testInstanceOfVoter()
    {
        $this->assertInstanceOf('Knp\Menu\Matcher\Voter\VoterInterface', $this->target);
    }

    /**
     * Test related method
     */
    public function testMatchItemWithSameUri()
    {
        $this->request
             ->expects($this->any())
             ->method('getRequestUri')
             ->will($this->returnValue('/foo'));

        $item = $this->getItemMock('/foo');

        $this->assertTrue($this->target->matchItem($item));
    }

    /**
     * Test related method
     */
    public function testMatchItemWithSamePatternUri()
    {
        $this->request
             ->expects($this->any())
             ->method('getRequestUri')
             ->will($this->returnValue('/foo/bar'));

        $item = $this->getItemMock('/foo');

        $this->assertTrue($this->target->matchItem($item));
    }

    /**
     * Test related method
     */
    public function testMatchItemWithUnrelatedUri()
    {
        $this->request
             ->expects($this->any())
             ->method('getRequestUri')
             ->will($this->returnValue('/bar'));

        $item = $this->getItemMock('/foo');

        $this->assertNull($this->target->matchItem($item));
    }

    /**
     * Test related method
     *
     * @return Mock
     */
    private function getRequestMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request', array('getRequestUri'));
    }

    /**
     * Test related method
     *
     * @param Request $request
     *
     * @return Mock
     */
    private function getContainerMock($request)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));

        $container->expects($this->any())
                  ->method('get')
                  ->with($this->equalTo('request'))
                  ->will($this->returnValue($request));

        return $container;
    }

    /**
     * Test related method
     *
     * @param string $uri
     *
     * @return Mock
     */
    private function getItemMock($uri)
    {
        $item = $this
            ->getMockBuilder('Knp\Menu\MenuItem')
            ->disableOriginalConstructor()
            ->setMethods(array('getUri'))
            ->getMock();

        $item->expects($this->any())
             ->method('getUri')
             ->will($this->returnValue($uri));

        return $item;
    }
}

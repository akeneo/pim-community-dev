<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Cache;

class EntityCacheClearerTest extends \PHPUnit_Framework_TestCase
{
    public function testClear()
    {
        $clearer = $this->getMockBuilder('Oro\Bundle\EmailBundle\Cache\EntityCacheClearer')
            ->setConstructorArgs(array('SomeDir', 'Test\SomeNamespace', 'Test%sProxy'))
            ->setMethods(array('createFilesystem'))
            ->getMock();

        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $clearer->expects($this->once())
            ->method('createFilesystem')
            ->will($this->returnValue($fs));

        // Temporary fix till EmailAddress will be moved to the cache folder
        // $fs->expects($this->once())
        //    ->method('remove')
        //    ->with($this->equalTo('SomeDir/Test/SomeNamespace/TestEmailAddressProxy.php'));

        $clearer->clear('');
    }
}

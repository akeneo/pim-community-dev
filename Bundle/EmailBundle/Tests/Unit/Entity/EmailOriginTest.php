<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Tests\Unit\ReflectionUtil;

class EmailOriginTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $entity = new EmailOrigin();
        ReflectionUtil::setId($entity, 1);
        $this->assertEquals(1, $entity->getId());
    }

    public function testNameGetterAndSetter()
    {
        $entity = new EmailOrigin();
        $entity->setName('test');
        $this->assertEquals('test', $entity->getName());
    }

    public function testFolderGetterAndSetter()
    {
        $folder = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailFolder');

        $entity = new EmailOrigin();
        $entity->addFolder($folder);

        $folders = $entity->getFolders();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $folders);
        $this->assertCount(1, $folders);
        $this->assertTrue($folder === $folders[0]);
    }
}

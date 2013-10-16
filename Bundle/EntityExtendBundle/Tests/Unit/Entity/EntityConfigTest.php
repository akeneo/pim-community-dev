<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\Entity\EntityConfig;

class EntityConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  EntityConfig */
    protected $entity;

    protected $config = array(
        'class'    => 'Oro\Bundle\UserBundle\Entity\User',
        'entity'   => 'Extend\Entity\ExtendUser',
        'type'     => 'Extend',
        'property' => array(),
        'doctrine' => array(
            'Extend\Entity\ExtendUser' => array(
                'type'       => 'mappedSuperclass',
                'fields'     => array(),
                'oneToMany'  => array(),
                'manyToOne'  => array(),
                'manyToMany' => array(),
            )
        ),
        'parent'   => 'Oro\Bundle\UserBundle\Model\ExtendUser',
        'inherit'  => ''
    );

    protected $active = false;

    public function setUp()
    {
        $this->entity = new EntityConfig(array(), false);
    }

    public function testProperties()
    {
        $this->assertNull($this->entity->getId());

        $this->assertEquals(false, $this->entity->getActive());

        $this->entity->setActive(true);
        $this->assertEquals(true, $this->entity->getActive());

        $this->entity->setConfig($this->config);
        $this->assertEquals($this->config, $this->entity->getConfig());

        $this->entity->prePersist();

        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->entity->setCreateAt($createdAt);
        $this->assertEquals($createdAt, $this->entity->getCreateAt());
    }
}

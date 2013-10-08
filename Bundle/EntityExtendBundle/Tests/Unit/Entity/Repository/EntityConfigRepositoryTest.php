<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\EntityManager;


use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EntityConfigRepository;

class EntityConfigRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityConfigRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $em;

    protected $config = array(
        'class'    => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
        'entity'   => 'Extend\Entity\ExtendContact',
        'type'     => 'Extend',
        'property' => array(),
        'doctrine' => array(
            'Extend\Entity\ExtendContact' => array(
                'type'       => 'mappedSuperclass',
                'fields'     => array(),
                'oneToMany'  => array(),
                'manyToOne'  => array(),
                'manyToMany' => array(),
            )
        ),
        'parent'   => 'OroCRM\Bundle\ContactBundle\Model\ExtendContact',
        'inherit'  => ''
    );

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder', 'beginTransaction', 'commit'))
            ->getMock();

        $this->repository = new EntityConfigRepository(
            $this->em,
            new ClassMetadata('Oro\Bundle\UserBundle\Entity\User')
        );
    }

    public function testCreateConfig()
    {
        $entityConfig = $this
            ->getMockBuilder('Oro\Bundle\EntityExtendBundle\Entity\Repository\EntityConfigRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('setActiveConfig'))
            ->getMock();

        //$entityConfig->expects($this->once())->method('setActiveConfig');
        //$this->repository->createConfig($this->config);
    }

    public function testSetActiveConfig()
    {

    }
}

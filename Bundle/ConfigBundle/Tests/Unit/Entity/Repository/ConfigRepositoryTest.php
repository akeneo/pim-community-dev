<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

class ConfigRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $om;

    /**
     * prepare mocks
     */
    public function setUp()
    {
        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $this->repository = $this->getMock(
            'Oro\Bundle\ConfigBundle\Entity\Repository\ConfigRepository',
            array('findOneBy'),
            array(
                $this->om,
                new ClassMetadata('Oro\Bundle\ConfigBundle\Entity\Config')
            )
        );

        //new ConfigRepository($this->om, new ClassMetadata('Oro\Bundle\ConfigBundle\Entity\Config'));
    }

    /**
     * data provider for loadSettings test
     */
    public function loadSettingsProvider()
    {
        return array(
            array(null, true),
            array('oro_user', false),
        );
    }

    /**
     * test loadSettings
     *
     * @dataProvider loadSettingsProvider
     */
    public function testLoadSettings($section, $isScope)
    {
        $criteria = array(
            'scopedEntity' => 'user',
            'recordId' => 1,
        );

        if ($isScope) {
            $value = $this->getMock('Oro\Bundle\ConfigBundle\Entity\ConfigValue');
            $value->expects($this->once())
                ->method('getSection')
                ->will($this->returnValue('oro_user'));
            $value->expects($this->once())
                ->method('getName')
                ->will($this->returnValue('level'));
            $value->expects($this->once())
                ->method('getValue')
                ->will($this->returnValue('test'));

            $scope = $this->getMock('Oro\Bundle\ConfigBundle\Entity\Config');
            $scope->expects($this->once())
                ->method('getValues')
                ->will($this->returnValue(array($value)));
            $scope->expects($this->once())
                ->method('getEntity')
                ->will($this->returnValue('user'));

            $this->repository
                ->expects($this->once())
                ->method('findOneBy')
                ->with($criteria)
                ->will($this->returnValue($scope));
        } else {
            $criteria['section'] = 'oro_user';

            $this->repository
                ->expects($this->once())
                ->method('findOneBy')
                ->with($criteria)
                ->will($this->returnValue(false));
        }

        $settings = $this->repository->loadSettings(
            $criteria['scopedEntity'],
            $criteria['recordId'],
            $section
        );

        if ($isScope) {
            $this->assertArrayHasKey('oro_user', $settings);
            $this->assertEquals('test', $settings['oro_user']['level']['value']);
        } else {
            $this->assertEmpty($settings);
        }
    }
}

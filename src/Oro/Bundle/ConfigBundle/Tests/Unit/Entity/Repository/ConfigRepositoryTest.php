<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\ConfigBundle\Entity\Repository\ConfigRepository;

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
    public function setUp(): void
    {
        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->repository = $this->createMock(
            ConfigRepository::class,
            ['findOneBy'],
            [
                $this->om,
                new ClassMetadata(Config::class)
            ]
        );

        //new ConfigRepository($this->om, new ClassMetadata(Config::class));
    }

    /**
     * data provider for loadSettings test
     */
    public function loadSettingsProvider()
    {
        return [
            [null, true],
            ['pim_user', false],
        ];
    }

    /**
     * test loadSettings
     *
     * @dataProvider loadSettingsProvider
     */
    public function testLoadSettings($section, $isScope)
    {
        $criteria = [
            'scopedEntity' => 'user',
            'recordId'     => 1,
        ];

        if ($isScope) {
            $value = $this->createMock(ConfigValue::class);
            $value->expects($this->once())
                ->method('getSection')
                ->will($this->returnValue('pim_user'));
            $value->expects($this->once())
                ->method('getName')
                ->will($this->returnValue('level'));
            $value->expects($this->once())
                ->method('getValue')
                ->will($this->returnValue('test'));

            $scope = $this->createMock(Config::class);
            $scope->expects($this->once())
                ->method('getValues')
                ->will($this->returnValue([$value]));
            $scope->expects($this->once())
                ->method('getEntity')
                ->will($this->returnValue('user'));

            $this->repository
                ->expects($this->once())
                ->method('findOneBy')
                ->with($criteria)
                ->will($this->returnValue($scope));
        } else {
            $criteria['section'] = 'pim_user';

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
            $this->assertArrayHasKey('pim_user', $settings);
            $this->assertEquals('test', $settings['pim_user']['level']['value']);
        } else {
            $this->assertEmpty($settings);
        }
    }
}

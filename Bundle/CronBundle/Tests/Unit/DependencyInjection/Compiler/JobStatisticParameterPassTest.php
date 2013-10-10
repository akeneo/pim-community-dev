<?php

namespace Oro\Bundle\CronBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CronBundle\DependencyInjection\Compiler\JobStatisticParameterPass;

class JobStatisticParameterPassTest extends \PHPUnit_Framework_TestCase
{
    /** @var JobStatisticParameterPass */
    protected $pass;

    public function setUp()
    {
        $this->pass = new JobStatisticParameterPass();
    }

    public function tearDown()
    {
        unset($this->pass);
    }

    /**
     * @dataProvider containerParamsProvider
     *
     * @param bool $isInstalled
     * @param bool $configValue
     * @param bool $expectedSet
     */
    public function testProcess($isInstalled, $configValue, $expectedSet)
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->at(0))->method('getParameter')->with('installed')
            ->will($this->returnValue($isInstalled));
        $containerBuilder->expects($this->at(1))->method('getParameter')->with('oro_cron.jms_statistics')
            ->will($this->returnValue($configValue));
        $containerBuilder->expects($this->at(2))->method('setParameter')->with(
            'jms_job_queue.statistics',
            $expectedSet
        );

        $this->pass->process($containerBuilder);
    }

    public function containerParamsProvider()
    {
        return array(
            'application not installed'                     => array(
                false,
                true,
                false
            ),
            'application is installed default config value' => array(
                '2013-09-26T18:50:00+03:00',
                true,
                true
            ),
            'application is installed pass config value'    => array(
                '2013-09-26T18:50:00+03:00',
                false,
                false
            )
        );
    }
}

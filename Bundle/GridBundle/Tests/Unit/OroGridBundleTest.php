<?php

namespace Oro\Bundle\GridBundle\Tests\Unit;

use Oro\Bundle\GridBundle\OroGridBundle;

class OroGridBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Compiler classes namespace
     */
    const COMPILER_NAMESPACE = 'Oro\Bundle\GridBundle\DependencyInjection\Compiler';

    /**
     * @var OroGridBundle
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new OroGridBundle();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testBuild()
    {
        $containerBuilderMock = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('addCompilerPass')
        );
        $containerBuilderMock->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(self::COMPILER_NAMESPACE . '\AddDependencyCallsCompilerPass'));
        $containerBuilderMock->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(self::COMPILER_NAMESPACE . '\AddFilterTypeCompilerPass'));

        $this->model->build($containerBuilderMock);
    }
}

<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit;

use Oro\Bundle\ImportExportBundle\OroImportExportBundle;

class OroImportExportBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroImportExportBundle
     */
    protected $bundle;

    protected function setUp()
    {
        $this->bundle = new OroImportExportBundle();
    }

    public function testBuild()
    {
        $containerBuilderMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('addCompilerPass'))
            ->getMock();
        $this->bundle->build($containerBuilderMock);
    }
}

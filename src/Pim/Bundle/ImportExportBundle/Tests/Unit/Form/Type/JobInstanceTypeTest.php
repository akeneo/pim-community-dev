<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Pim\Bundle\EnrichBundle\Tests\Unit\Form\Type\AbstractFormTypeTest;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;
use Pim\Bundle\ImportExportBundle\Form\Type\JobConfigurationType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceTypeTest extends AbstractFormTypeTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $connector = $this->getConnectorRegistryMock();
        $this->type = new JobInstanceType($connector);
        $this->form = $this->factory->create($this->type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $jobType = new JobConfigurationType();

        return array(
            new PreloadedExtension(
                array(
                    $jobType->getName() => $jobType,
                ),
                array()
            )
        );
    }

    /**
     * Get connector registry mock
     *
     * @return \Oro\Bundle\BatchBundle\Connector\ConnectorRegistry
     */
    protected function getConnectorRegistryMock()
    {
        return $this
            ->getMockBuilder('\Oro\Bundle\BatchBundle\Connector\ConnectorRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');
        $this->assertField('label', 'text');
        $this->assertField('job', 'pim_import_export_job_configuration');

        // Assert name
        $this->assertEquals('pim_import_export_jobInstance', $this->form->getName());
    }
}

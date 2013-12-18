<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Form\Type;

use Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type\AbstractFormTypeTest;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;

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

        // create form type
        $connector = $this->getConnectorRegistryMock();
        $this->type = new JobInstanceType($connector);
        $this->form = $this->factory->create($this->type);
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
        $this->assertField('job', 'oro_batch_job_configuration');

        // Assert name
        $this->assertEquals('pim_import_export_jobInstance', $this->form->getName());
    }
}

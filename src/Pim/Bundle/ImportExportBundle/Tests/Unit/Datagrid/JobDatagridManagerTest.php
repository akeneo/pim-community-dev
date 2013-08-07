<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Datagrid;

use Symfony\Component\Translation\Translator;
use Oro\Bundle\GridBundle\Tests\Unit\Datagrid\DatagridManagerTest;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobDatagridManagerTest extends DatagridManagerTest
{
    /**
     * Test related method
     */
    public function testConfigureFields()
    {
        $this->markTestIncomplete("The 'configureFields' method is protected");
        $datagridManager = $this->createDatagridManager();

        $expectedFields = $this->getExpectedFields();

        $fieldsCollection = new FieldDescriptionCollection();
        $datagridManager->configureFields($fieldsCollection);

        $this->assertCount(count($expectedFields), $fieldsCollection);
        foreach ($fieldsCollection as $fieldDescription) {
            $this->assertContains($fieldDescription->getName(), $expectedFields);
        }
    }

    /**
     * Create translator
     *
     * @return \Symfony\Component\Translation\Translator
     */
    protected function createTranslator()
    {
        return new Translator('en_US');
    }

    /**
     * Get list of expected fields
     *
     * @return array
     */
    protected function getExpectedFields()
    {
        return array('code', 'label', 'connector', 'status');
    }

    /**
     * Create datagrid manager
     *
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    protected function createDatagridManager()
    {
        $datagridManager = new JobDatagridManager();
        $datagridManager->setTranslator($this->createTranslator());
        $datagridManager->setJobType('export');

        return $datagridManager;
    }
}

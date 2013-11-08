<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class EntityTest extends Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected function setUp()
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(intval(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT));
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
    }

    protected function tearDown()
    {
        $this->cookie()->clear();
    }

    /**
     * @return string
     */
    public function testCreateEntity()
    {
        $entityname = 'Entity'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openConfigEntities()
            ->add()
            ->assertTitle('New Entity - Entities - System')
            ->setName($entityname)
            ->setLabel($entityname)
            ->setPluralLabel($entityname)
            ->save()
            ->assertMessage('Entity saved')
            ->createField()
            ->setFieldName('Test_field')
            ->setType('String')
            ->proceed()
            ->save()
            ->assertMessage('Field saved')
            ->updateSchema()
            ->close();

        return $entityname;
    }

    /**
     * @depends testCreateEntity
     * @param $entityname
     * @return string
     */
    public function testUpdateEntity($entityname)
    {
        $newentityname = 'Update' . $entityname;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openConfigEntities()
            ->filterBy('Label', $entityname)
            ->open(array($entityname))
            ->edit()
            ->setLabel($newentityname)
            ->save()
            ->assertMessage('Entity saved')
            ->assertTitle($newentityname .' - Entities - System')
            ->createField()
            ->setFieldName('Test_field2')
            ->setType('Integer')
            ->proceed()
            ->save()
            ->assertMessage('Field saved')
            ->updateSchema();

        return $newentityname;
    }

    /**
     * @depends testUpdateEntity
     * @param $entityname
     */
    public function testEntityFieldsAvailability($entityname)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openNavigation()
            ->tab('System')
            ->menu('Entities')
            ->menu($entityname)
            ->open()
            ->openConfigEntity()
            ->newCustomEntityAdd()
            ->checkEntityField('Test_field')
            ->checkEntityField('Test_field2');
    }

    /**
     * @depends testUpdateEntity
     * @param $entityname
     */
    public function testDeleteEntity($entityname)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openConfigEntities()
            ->filterBy('Label', $entityname)
            ->delete()
            ->assertMessage('Item was removed')
            ->open(array($entityname))
            ->updateSchema()
            ->close()
            ->filterBy('Label', $entityname)
            ->assertNoDataMessage('No entity was found to match your search.');
    }
}

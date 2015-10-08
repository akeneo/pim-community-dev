<?php

namespace ConfigBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\ConfigBundle\Form\Type\ConfigCheckbox;

class ConfigCheckboxTest extends FormIntegrationTestCase
{
    /**
     * @var ConfigCheckbox
     */
    protected $formType;

    protected function setUp()
    {
        parent::setUp();
        $this->formType = new ConfigCheckbox();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->formType);
    }

    public function testGetName()
    {
        $this->assertEquals(ConfigCheckbox::NAME, $this->formType->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('checkbox', $this->formType->getParent());
    }

    /**
     * @param mixed $source
     * @param mixed $expected
     * @dataProvider buildFormDataProvider
     */
    public function testBuildForm($source, $expected)
    {
        $form = $this->factory->create($this->formType);
        $form->setData($source);
        $this->assertEquals($expected, $form->getData());
    }

    /**
     * @return array
     */
    public function buildFormDataProvider()
    {
        return array(
            'valid true' => array(
                'source' => true,
                'expected' => true,
            ),
            'valid false' => array(
                'source' => false,
                'expected' => false,
            ),
            'empty string' => array(
                'source' => '',
                'expected' => false,
            ),
            'string 0' => array(
                'source' => '0',
                'expected' => false,
            ),
            'string 1' => array(
                'source' => '1',
                'expected' => true,
            ),
        );
    }
}

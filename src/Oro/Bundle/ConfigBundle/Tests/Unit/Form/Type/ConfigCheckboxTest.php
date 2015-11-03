<?php

namespace ConfigBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Form\Type\ConfigCheckbox;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

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
        return [
            'valid true' => [
                'source'   => true,
                'expected' => true,
            ],
            'valid false' => [
                'source'   => false,
                'expected' => false,
            ],
            'empty string' => [
                'source'   => '',
                'expected' => false,
            ],
            'string 0' => [
                'source'   => '0',
                'expected' => false,
            ],
            'string 1' => [
                'source'   => '1',
                'expected' => true,
            ],
        ];
    }
}

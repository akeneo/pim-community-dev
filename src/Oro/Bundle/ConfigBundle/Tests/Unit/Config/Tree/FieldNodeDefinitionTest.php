<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Config\Tree;

use Oro\Bundle\ConfigBundle\Config\Tree\FieldNodeDefinition;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class FieldNodeDefinitionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'testNodeName';
    const TEST_TYPE = 'text';
    const TEST_ACL = 'acl';

    protected $testDefinition = [
        'options'      => [
            'some_opt' => 'some_value'
        ],
        'type'         => self::TEST_TYPE,
        'acl_resource' => self::TEST_ACL
    ];

    public function testGetType()
    {
        $node = new FieldNodeDefinition(self::TEST_NAME, $this->testDefinition);

        $this->assertEquals(self::TEST_TYPE, $node->getType());
    }

    public function testGetAclResource()
    {
        // acl resource specified
        $node = new FieldNodeDefinition(self::TEST_NAME, $this->testDefinition);
        $this->assertEquals(self::TEST_ACL, $node->getAclResource());

        // acl resource not specified, should return false
        $node = new FieldNodeDefinition(self::TEST_NAME, []);
        $this->assertFalse($node->getAclResource());
    }

    public function testGetOptions()
    {
        // options come from definition
        $node = new FieldNodeDefinition(self::TEST_NAME, $this->testDefinition);
        $this->assertEquals(self::TEST_ACL, $node->getAclResource());

        // options come from setter
        $options = ['another_opt' => 'another_value'];

        $node = new FieldNodeDefinition(self::TEST_NAME, []);
        $node->setOptions($options);
        $this->assertEquals($options, $node->getOptions());

        // option override
        $node->replaceOption('another_opt', 'newValue');
        $options = $node->getOptions();
        $this->assertArrayHasKey('another_opt', $options);
        $this->assertEquals('newValue', $options['another_opt']);
    }

    public function testToFormFieldOptions()
    {
        $node = new FieldNodeDefinition(self::TEST_NAME, $this->testDefinition);

        $result = $node->toFormFieldOptions();

        $this->assertArrayHasKey('target_field', $result);
        $this->assertEquals($node, $result['target_field']);
        $this->assertArrayNotHasKey('some_opt', $result);

        $options = [
            'label'    => 'someLabel',
            'required' => true,
            'block'    => 'some_block',
            'subblock' => 'some_subblock'
        ];
        $node->setOptions($options);

        $result = $node->toFormFieldOptions();
        foreach ($options as $optionName => $value) {
            $this->assertArrayHasKey($optionName, $result);
            $this->assertEquals($value, $result[$optionName]);
        }
    }

    public function testPrepareDefinition()
    {
        $node = new FieldNodeDefinition(self::TEST_NAME, []);

        // should set default definition values
        $this->assertEquals(0, $node->getPriority());
        $this->assertInternalType('array', $node->getOptions());
    }

    /**
     * @dataProvider constraintsProvider
     *
     * @param array $definition
     * @param array $expected
     */
    public function testPrepareValidators($definition, $expected)
    {
        $node = new FieldNodeDefinition(self::TEST_NAME, $definition);
        $result = $node->getOptions();

        $this->assertArrayHasKey('constraints', $result);
        $this->assertEquals($expected, $result['constraints']);
    }

    /**
     * @return array
     */
    public function constraintsProvider()
    {
        $notBlank = new NotBlank();
        $length = new Length(['min' => 1, 'max' => 2]);

        return [
            'constraints empty' => [
                'definition' => [
                    'options' => [
                        'constraints' => []
                    ]
                ],
                'expected' => []
            ],
            'constraints comes as strings' => [
                'definition' => [
                    'options' => [
                        'constraints' => [
                            [
                                'NotBlank' => null
                            ]
                        ]
                    ]
                ],
                'expected' => [$notBlank]
            ],
            'constraints comes as full class names' => [
                'definition' => [
                    'options' => [
                        'constraints' => [
                            [
                                'Symfony\Component\Validator\Constraints\Length' => [
                                    'min' => 1,
                                    'max' => 2,
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => [$length]
            ]
        ];
    }
}

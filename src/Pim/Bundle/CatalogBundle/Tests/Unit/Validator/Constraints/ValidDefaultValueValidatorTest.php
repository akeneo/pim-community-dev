<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidDefaultValueValidator;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidDefaultValue;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Valid default value validator test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidDefaultValueValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->constraint = new ValidDefaultValue();
        $this->validator = new ValidDefaultValueValidator();
        $this->validator->initialize($this->context);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;

        parent::tearDown();
    }

    /**
     * Provider for no default value violations
     * @return array
     */
    public static function providerDefaultValueValid()
    {
        return array(
            array(
                'pim_catalog_date',
                array(
                    'defaultValue' => new \DateTime('+1 month'),
                    'dateType'     => 'datetime',
                    'dateMin'      => new \DateTime('now'),
                    'dateMax'      => new \DateTime('+1 year')
                )
            ),
            array(
                'pim_catalog_price_collection',
                array(
                    'defaultValue'    => 9.99,
                    'numberMin'       => 0.01,
                    'numberMax'       => 1000000,
                    'decimalsAllowed' => true,
                )
            ),
            array(
                'pim_catalog_number',
                array(
                    'defaultValue'    => -10,
                    'numberMin'       => -100.1,
                    'numberMax'       => 100,
                    'decimalsAllowed' => true,
                    'negativeAllowed' => true
                )
            ),
            array(
                'pim_catalog_textarea',
                array(
                    'defaultValue'  => 'test value',
                    'maxCharacters' => 200
                )
            ),
            array(
                'pim_catalog_metric',
                array(
                    'defaultValue'      => 20,
                    'numberMin'         => -273,
                    'numberMax'         => 1000,
                    'decimalsAllowed'   => false,
                    'negativeAllowed'   => true,
                    'metricFamily'      => 'temperature',
                    'defaultMetricUnit' => 'C'
                )
            ),
            array(
                'pim_catalog_text',
                array(
                    'defaultValue'     => 'Test123',
                    'maxCharacters'    => 100,
                    'validationRule'   => 'regexp',
                    'validationRegexp' => '#[[:alnum:]]#'
                )
            ),
            array(
                'pim_catalog_text',
                array(
                    'defaultValue'   => 'user@sub.domain.museum',
                    'validationRule' => 'email'
                )
            ),
            array(
                'pim_catalog_text',
                array(
                    'defaultValue'   => 'http://symfony.com/',
                    'validationRule' => 'url'
                )
            )
        );
    }

    /**
     * Test case without default value violations
     * @param string $attributeType
     * @param array  $properties
     *
     * @dataProvider providerDefaultValueValid
     */
    public function testDefaultValueValid($attributeType, $properties)
    {
        $attribute = $this->createAttribute($attributeType, $properties);

        $this->context->expects($this->never())->method('addViolationAt');

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for inivalid default value
     * @return array
     */
    public static function providerDefaultValueInvalid()
    {
        return array(
            array(
                'pim_catalog_date',
                array(
                    'defaultValue' => new \DateTime('now'),
                    'dateMin'      => new \DateTime('+1 day'),
                    'dateMax'      => new \DateTime('+1 month')
                ),
                'This value should be between the min and max date.'
            ),
            array(
                'pim_catalog_price_collection',
                array(
                    'defaultValue' => 1,
                    'numberMin'    => 5.5,
                ),
                'This value should be between the min and max number.'
            ),
            array(
                'pim_catalog_number',
                array(
                    'defaultValue'    => -100,
                    'negativeAllowed' => false
                ),
                'This value should be greater than or equal to 0'
            ),
            array(
                'pim_catalog_metric',
                array(
                    'defaultValue'    => 0.1,
                    'decimalsAllowed' => false
                ),
                'This value should be a whole number.'
            ),
            array(
                'pim_catalog_textarea',
                array(
                    'defaultValue'  => 'test value',
                    'maxCharacters' => 5
                ),
                'This value should not exceed max characters.'
            ),
            array(
                'pim_catalog_date',
                array(
                    'defaultValue'     => 'Test123'
                ),
                'This date format is not valid.'
            ),
            array(
                'pim_catalog_text',
                array(
                    'defaultValue'     => 'Some text',
                    'validationRule'   => 'regexp',
                    'validationRegexp' => '/^\d+$/'
                ),
                'This value should match the regular expression.'
            )
        );
    }

    /**
     * Test case with invalid default value
     * @param string $attributeType
     * @param array  $properties
     * @param string $message
     *
     * @dataProvider providerDefaultValueInvalid
     */
    public function testDefaultValueInvalid($attributeType, $properties, $message)
    {
        $attribute = $this->createAttribute($attributeType, $properties);

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with($this->constraint->propertyPath, $message);

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Create a Attribute entity
     * @param string $attributeType
     * @param array  $properties
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected function createAttribute($attributeType, $properties = array())
    {
        $attribute = new Attribute();

        $attribute->setAttributeType($attributeType);

        foreach ($properties as $property => $value) {
            $set = 'set' . ucfirst($property);
            if (method_exists($attribute, $set)) {
                $attribute->$set($value);
            }
        }

        return $attribute;
    }
}

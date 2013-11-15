<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new UniqueValueGuesser();
    }

    /**
     * Test related method
     */
    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    /**
     * Data provider for supported attributes
     * array(
     *     Attribute backend type,
     *     Boolean to know if supported or not
     * )
     *
     *
     * @static
     *
     * @return array[]
     */
    public static function dataProviderForSupportedAttributes()
    {
        return array(
            'boolean'    => array(AbstractAttributeType::BACKEND_TYPE_BOOLEAN, false),
            'collection' => array(AbstractAttributeType::BACKEND_TYPE_COLLECTION, false),
            'date'       => array(AbstractAttributeType::BACKEND_TYPE_DATE, true),
            'datetime'   => array(AbstractAttributeType::BACKEND_TYPE_DATETIME, true),
            'decimal'    => array(AbstractAttributeType::BACKEND_TYPE_DECIMAL, true),
            'entity'     => array(AbstractAttributeType::BACKEND_TYPE_ENTITY, false),
            'integer'    => array(AbstractAttributeType::BACKEND_TYPE_INTEGER, true),
            'media'      => array(AbstractAttributeType::BACKEND_TYPE_MEDIA, false),
            'metric'     => array(AbstractAttributeType::BACKEND_TYPE_METRIC, false),
            'option'     => array(AbstractAttributeType::BACKEND_TYPE_OPTION, false),
            'options'    => array(AbstractAttributeType::BACKEND_TYPE_OPTIONS, false),
            'price'      => array(AbstractAttributeType::BACKEND_TYPE_PRICE, false),
            'text'       => array(AbstractAttributeType::BACKEND_TYPE_TEXT, false),
            'varchar'    => array(AbstractAttributeType::BACKEND_TYPE_VARCHAR, true),
        );
    }

    /**
     * Test related method
     * @param string  $backendType
     * @param boolean $expectedAvailability
     *
     * @dataProvider dataProviderForSupportedAttributes
     */
    public function testSupportVarcharAttribute($backendType, $expectedAvailability)
    {
        $result = $this->target->supportAttribute(
            $this->getAttributeMock(array('backendType' => $backendType))
        );

        $this->assertEquals($expectedAvailability, $result);
    }

    /**
     * Test related method
     */
    public function testGuessUniqueValueConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
                    'unique'      => true,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue', $constraints);
    }

    /**
     * Test related method
     */
    public function testDoNotGuessRangeConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
                    'unique'      => false,
                )
            )
        );

        $this->assertEquals(0, count($constraints));
    }
}

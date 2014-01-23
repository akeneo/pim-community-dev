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
     * @return array[]
     */
    public static function dataProviderForSupportedAttributes()
    {
        return [
            'boolean'    => [AbstractAttributeType::BACKEND_TYPE_BOOLEAN, false],
            'collection' => [AbstractAttributeType::BACKEND_TYPE_COLLECTION, false],
            'date'       => [AbstractAttributeType::BACKEND_TYPE_DATE, true],
            'datetime'   => [AbstractAttributeType::BACKEND_TYPE_DATETIME, true],
            'decimal'    => [AbstractAttributeType::BACKEND_TYPE_DECIMAL, true],
            'entity'     => [AbstractAttributeType::BACKEND_TYPE_ENTITY, false],
            'integer'    => [AbstractAttributeType::BACKEND_TYPE_INTEGER, true],
            'media'      => [AbstractAttributeType::BACKEND_TYPE_MEDIA, false],
            'metric'     => [AbstractAttributeType::BACKEND_TYPE_METRIC, false],
            'option'     => [AbstractAttributeType::BACKEND_TYPE_OPTION, false],
            'options'    => [AbstractAttributeType::BACKEND_TYPE_OPTIONS, false],
            'price'      => [AbstractAttributeType::BACKEND_TYPE_PRICE, false],
            'text'       => [AbstractAttributeType::BACKEND_TYPE_TEXT, false],
            'varchar'    => [AbstractAttributeType::BACKEND_TYPE_VARCHAR, true],
        ];
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
            $this->getAttributeMock(['backendType' => $backendType])
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
                [
                    'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
                    'unique'      => true,
                ]
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
                [
                    'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
                    'unique'      => false,
                ]
            )
        );

        $this->assertEquals(0, count($constraints));
    }
}

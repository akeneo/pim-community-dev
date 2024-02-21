<?php

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\Validation;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeGroupValidationIntegration extends TestCase
{
    public function testAttributeGroupUniqueEntity()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update($attributeGroup, ['code' => 'attributeGroupA']);

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value is already used.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAttributeGroupImmutableCode()
    {
        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('attributeGroupA');
        $this->getUpdater()->update($attributeGroup, ['code' => 'attributeGroupAA']);

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAttributeGroupTranslationsLength()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code'   => 'attributeGroup',
                'labels' => [
                    'en_US' => str_pad('long_label', 101, "_"),
                ],
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value is too long. It should have 100 characters or less.',
            $violation->getMessage()
        );
        $this->assertSame('translations[0].label', $violation->getPropertyPath());
    }

    public function testAttributeGroupTranslationsLocale()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code'   => 'attributeGroup',
                'labels' => [
                    'en_FR' => 'Attribute group',
                ],
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The locale "en_FR" does not exist.',
            $violation->getMessage()
        );
        $this->assertSame('translations[0].locale', $violation->getPropertyPath());
    }

    public function testAttributeGroupSortOrderNotNull()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code'       => 'attributeGroup',
                'sort_order' => null,
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should not be null.',
            $violation->getMessage()
        );
        $this->assertSame('sortOrder', $violation->getPropertyPath());
    }

    public function testAttributeGroupSortOrderTypeNumeric()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code'       => 'attributeGroup',
                'sort_order' => 'test',
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should be of type numeric.',
            $violation->getMessage()
        );
        $this->assertSame('sortOrder', $violation->getPropertyPath());
    }

    public function testAttributeGroupSortOrderNotDecimal()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code'       => 'attributeGroup',
                'sort_order' => 1.5,
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should not be a decimal.',
            $violation->getMessage()
        );
        $this->assertSame('sortOrder', $violation->getPropertyPath());
    }

    public function testAttributeGroupSortOrderGreaterThanOrEqualZero()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code'       => 'attributeGroup',
                'sort_order' => -1,
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should be greater than or equal to 0.',
            $violation->getMessage()
        );
        $this->assertSame('sortOrder', $violation->getPropertyPath());
    }

    public function testAttributeGroupCodeNotBlank()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            []
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should not be blank.',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAttributeGroupCodeRegex()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code' => 'attribute-group',
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'Attribute group code may contain only letters, numbers and underscores',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAttributeGroupCodeLength()
    {
        $attributeGroup = $this->createAttributeGroup();
        $this->getUpdater()->update(
            $attributeGroup,
            [
                'code' => str_pad('longCode', 101, "l"),
            ]
        );

        $violations = $this->getValidator()->validate($attributeGroup);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value is too long. It should have 100 characters or less.',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * @return AttributeGroupInterface
     */
    private function createAttributeGroup()
    {
        return $this->get('pim_catalog.factory.attribute_group')->create();
    }

    /**
     * @return ObjectUpdaterInterface
     */
    private function getUpdater()
    {
        return $this->get('pim_catalog.updater.attribute_group');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

}

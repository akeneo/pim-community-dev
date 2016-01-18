<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Validator\Import;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductImportValidatorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        ConstraintGuesserInterface $constraintGuesser,
        ProductManager $productManager
    ) {
        $this->beConstructedWith($validator, $constraintGuesser, $productManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Validator\Import\ProductImportValidator');
    }

    function it_is_an_import_validator()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface');
    }

    function it_checks_unicity_of_product_value(
        Product $product1,
        Product $product2,
        ColumnInfo $columnInfo1,
        ColumnInfo $columnInfo2,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        ProductValueInterface $productValue1,
        ProductValueInterface $productValue2,
        ProductValueInterface $productValue3,
        ProductValueInterface $productValue4,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraint
    ) {
        $values =
        [
            [
                'sku' => 'AKNTS_BPXL',
                'test_unique_attribute' => '1200000011a'
            ],

            [
                'sku' => '17727158',
                'test_unique_attribute' => '1200000011a'
            ]
        ];

        $columnInfo1->getLabel()->willReturn("sku");
        $columnInfo1->getName()->willReturn("sku");
        $columnInfo1->getLocale()->willReturn(null);
        $columnInfo1->getScope()->willReturn(null);
        $columnInfo1->getAttribute()->willReturn($attribute1);

        $columnInfo2->getLabel()->willReturn("test_unique_attribute");
        $columnInfo2->getName()->willReturn("test_unique_attribute");
        $columnInfo2->getLocale()->willReturn(null);
        $columnInfo2->getScope()->willReturn(null);
        $columnInfo2->getAttribute()->willReturn($attribute2);

        $attribute1->isUnique()->willReturn(true);
        $attribute1->getAttributeType()->willReturn('pim_catalog_text');
        $attribute1->getCode()->willReturn('sku');

        $attribute2->isUnique()->willReturn(true);
        $attribute2->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute2->getCode()->willReturn('test_unique_attribute');

        $columnsInfo = [$columnInfo1, $columnInfo2];

        $product1->getValue("sku", null, null)->shouldBeCalled()->willReturn($productValue1);
        $product1->getValue("test_unique_attribute", null, null)->shouldBeCalled()->willReturn($productValue2);

        $product2->getValue("sku", null, null)->shouldBeCalled()->willReturn($productValue3);
        $product2->getValue("test_unique_attribute", null, null)->shouldBeCalled()->willReturn($productValue4);

        $product1->__toString()->willReturn('product1');
        $product2->__toString()->willReturn('product2');

        $productValue1->getAttribute()->willReturn($attribute1);
        $productValue2->getAttribute()->willReturn($attribute2);

        $productValue3->getAttribute()->willReturn($attribute1);
        $productValue4->getAttribute()->willReturn($attribute2);

        $productValue1->getData()->willReturn("AKNTS_BPXL");
        $productValue2->getData()->willReturn("1200000011a");

        $productValue3->getData()->willReturn("17727158");
        $productValue4->getData()->willReturn("1200000011a");

        $productValue1->__toString()->willReturn("AKNTS_BPXL");
        $productValue2->__toString()->willReturn("1200000011a");

        $productValue3->__toString()->willReturn("17727158");
        $productValue4->__toString()->willReturn("1200000011a");

        $validator->validate('17727158', Argument::any())->shouldBeCalled()->willReturn($constraint);
        $validator->validate('1200000011a', Argument::any())->shouldBeCalled()->willReturn($constraint);
        $validator->validate('AKNTS_BPXL', Argument::any())->shouldBeCalled()->willReturn($constraint);

        $validator->validate($product1, Argument::any())->shouldBeCalled()->willReturn($constraint);
        $validator->validate($product2, Argument::any())->shouldBeCalled()->willReturn($constraint);
        $constraint->count()->willReturn(0);

        $errors = [
            '17727158' => [
                ['The value "1200000011a" for unique attribute "test_unique_attribute" was already read in this file']
            ]
        ];

        $this->validate($product1, $columnsInfo, $values[0])->shouldReturn([]);
        $this->validate($product2, $columnsInfo, $values[1])->shouldReturn($errors);
    }
}

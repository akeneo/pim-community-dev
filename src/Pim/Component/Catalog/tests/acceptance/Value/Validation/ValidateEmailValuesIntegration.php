<?php

declare(strict_types = 1);

namespace Pim\Component\Catalog\tests\acceptance\Value\Validation;

use Akeneo\Test\Integration\Catalog\InMemoryAttributeRepository;
use Pim\Component\Catalog\AttributeTypes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValidateEmailValuesIntegration extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel(['debug' => false]);

        $attributeRepository = new InMemoryAttributeRepository();

        static::$kernel->getContainer()->set('pim_catalog.saver.attribute', $attributeRepository);
        static::$kernel->getContainer()->set('pim_catalog.repository.attribute', $attributeRepository);

        $attributeFactory = static::$kernel->getContainer()->get('pim_catalog.factory.attribute');
        $identifierAttribute = $attributeFactory->createAttribute(AttributeTypes::IDENTIFIER);
        $identifierAttribute->setCode('sku');
        $attributeRepository->save($identifierAttribute);
    }

    public function testAcceptCorrectEmailTextValue()
    {
        $productBuilder = static::$kernel->getContainer()->get('pim_catalog.builder.product');
        $attributeFactory = static::$kernel->getContainer()->get('pim_catalog.factory.attribute');

        $emailAttribute = $attributeFactory->createAttribute(AttributeTypes::TEXT);
        $emailAttribute->setCode('email');
        $emailAttribute->setValidationRule('email');

        $product = $productBuilder->createProduct('new_product');
        $productBuilder->addOrReplaceValue($product, $emailAttribute, null, null, 'michel@akeneo.com');

        $errors = static::$kernel->getContainer()->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $errors->count());
    }

    public function testRejectIncorrectEmailTextValue()
    {
        $productBuilder = static::$kernel->getContainer()->get('pim_catalog.builder.product');
        $attributeFactory = static::$kernel->getContainer()->get('pim_catalog.factory.attribute');

        $emailAttribute = $attributeFactory->createAttribute(AttributeTypes::TEXT);
        $emailAttribute->setCode('email');
        $emailAttribute->setValidationRule('email');

        $product = $productBuilder->createProduct('new_product');
        $productBuilder->addOrReplaceValue($product, $emailAttribute, null, null, 'not an email address');

        $errors = static::$kernel->getContainer()->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('This value is not a valid email address.', $errors[0]->getMessage());
    }
}

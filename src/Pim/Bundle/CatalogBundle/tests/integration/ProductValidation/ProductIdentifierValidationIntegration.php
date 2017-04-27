<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\ProductValidation;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 *            This field should not contain any comma or semicolon.
 */
class ProductIdentifierValidationIntegration extends TestCase
{
    public function testUniqueIdentifierValidation()
    {
        $product1 = $this->createProduct('just_an_empty_product');
        $violations = $this->validateProduct($product1);

        $this->assertCount(0, $violations);

        $this->saveProduct($product1);

        $product2 = $this->createProduct('just_an_empty_product');
        $violations = $this->validateProduct($product2);

        $this->assertCount(1, $violations);
        $this->assertSame($violations->get(0)->getMessage(), 'The same identifier is already set on another product');
    }

    public function testForbiddenIdentifierCharactersValidation()
    {
        $product1 = $this->createProduct('a,product,with,comma');
        $product2 = $this->createProduct('a;product;with;semi-column');

        $product1Violations = $this->validateProduct($product1);
        $product2Violations = $this->validateProduct($product2);

        $this->assertCount(1, $product1Violations);
        $this->assertCount(1, $product2Violations);
        $this->assertSame(
            $product1Violations->get(0)->getMessage(),
            'This field should not contain any comma or semicolon.'
        );
        $this->assertSame(
            $product1Violations->get(0)->getMessage(),
            'This field should not contain any comma or semicolon.'
        );
    }

    public function testMaxCharactersValidation()
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('sku');
        $attribute->setMaxCharacters(4);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $correctProduct = $this->createProduct('1234');
        $violations = $this->validateProduct($correctProduct);
        $this->assertCount(0, $violations);

        $wrongProduct = $this->createProduct('12345');
        $violations = $this->validateProduct($wrongProduct);
        $this->assertCount(1, $violations);
        $this->assertSame(
            $violations->get(0)->getMessage(),
            'This value is too long. It should have 4 characters or less.'
        );
    }

    public function testRegexValidation()
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('sku');
        $attribute->setValidationRule('regexp');
        $attribute->setValidationRegexp('/^sku-\d*$/');
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $correctProduct = $this->createProduct('sku-001');
        $violations = $this->validateProduct($correctProduct);
        $this->assertCount(0, $violations);

        $wrongProduct = $this->createProduct('001');
        $violations = $this->validateProduct($wrongProduct);
        $this->assertCount(1, $violations);
        $this->assertSame(
            $violations->get(0)->getMessage(),
            'This value is not valid.'
        );
    }

    public function testNotBlankValidation()
    {
        $correctProduct = $this->createProduct('sku-001');
        $violations = $this->validateProduct($correctProduct);
        $this->assertCount(0, $violations);

        $wrongProduct = $this->createProduct('');
        $violations = $this->validateProduct($wrongProduct);
        $this->assertCount(1, $violations);
        $this->assertSame(
            $violations->get(0)->getMessage(),
            'This value should not be blank.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            true
        );
    }

    /**
     * @param string $productIdentifier
     *
     * @return ProductInterface
     */
    private function createProduct($productIdentifier)
    {
        return $this->get('pim_catalog.builder.product')->createProduct($productIdentifier);
    }

    /**
     * @param ProductInterface $product
     *
     * @return ConstraintViolationListInterface
     */
    private function validateProduct(ProductInterface $product)
    {
        return $this->get('pim_catalog.validator.product')->validate($product);
    }

    /**
     * @param ProductInterface $product
     */
    private function saveProduct(ProductInterface $product)
    {
        $this->get('pim_catalog.saver.product')->save($product);
    }
}

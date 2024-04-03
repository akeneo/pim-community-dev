<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
        $this->assertSame($violations->get(0)->getMessage(), 'The just_an_empty_product identifier is already used for another product.');
        $this->assertSame($violations->get(0)->getPropertyPath(), 'identifier');
    }

    public function testLineBreakInIdentifierValidation()
    {
        $product = $this->createProduct("foo" . PHP_EOL);
        $violations = $this->validateProduct($product);

        $this->assertCount(1, $violations);
        $this->assertSame($violations->get(0)->getMessage(), 'This field should not contain any line break');
        $this->assertSame($violations->get(0)->getPropertyPath(), 'identifier');

        $this->saveProduct($product);
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
            'The identifier attribute must not contain more than 4 characters. The submitted value is too long.'
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
            'The sku attribute must match the following regular expression: /^sku-\d*$/.'
        );
    }

    public function testItCannotCreateAnIdentifierWithNonBreakingSpaceCharacter()
    {
        $correctProduct = $this->createProduct('sku-001 ');
        $violations = $this->validateProduct($correctProduct);

        $this->assertCount(1, $violations);
        $this->assertSame(
            $violations->get(0)->getMessage(),
            'This field should not contain any comma or semicolon or leading/trailing space'
        );
    }

    public function testItCanCreateProductWithoutIdentifier()
    {
        $correctProduct = $this->createProduct('sku-001');
        $violations = $this->validateProduct($correctProduct);
        $this->assertCount(0, $violations);

        $productWithoutIdentifier = $this->createProduct('');
        $violations = $this->validateProduct($productWithoutIdentifier);
        $this->assertCount(0, $violations);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
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

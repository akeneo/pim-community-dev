<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Context;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Behat\Behat\Context\Context;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Akeneo\Test\Common\Structure\Attribute;

/**
 * Use this context to create products
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCreation implements Context
{
    private const IDENTIFIER_ATTRIBUTE = 'sku';

    /** @var Builder\Product */
    private $productBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Attribute\Builder */
    private $attributeBuilder;

    public function __construct(
        SaverInterface $attributeSaver,
        Builder\Product $productBuilder,
        InMemoryProductRepository $productRepository,
        Attribute\Builder $attributeBuilder
    ) {
        $this->attributeSaver = $attributeSaver;
        $this->productBuilder = $productBuilder;
        $this->productRepository = $productRepository;
        $this->attributeBuilder = $attributeBuilder;
    }

    /**
     * @Given a product with an identifier :identifier
     */
    public function aProductWithAnIdentifier(string $identifier): void
    {
        $attribute = $this->attributeBuilder->aIdentifier()
            ->withCode(self::IDENTIFIER_ATTRIBUTE)
            ->build();

        $this->attributeSaver->save($attribute);

        $product = $this->productBuilder->withIdentifier($identifier)->build();
        $this->productRepository->save($product);
    }
}

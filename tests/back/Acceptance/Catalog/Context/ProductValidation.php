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
 * Use this context to check product validation rules. Create a product with specific values, valid the product
 * object and check errors.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductValidation implements Context
{
    /** @var ProductInterface */
    private $updatedProduct;

    /** @var Builder\Product */
    private $productBuilder;

    /** @var ValidatorInterface */
    private $productValidator;

    public function __construct(
        Builder\Product $productBuilder,
        ValidatorInterface $productValidator
    ) {
        $this->productBuilder = $productBuilder;
        $this->productValidator = $productValidator;
    }

    /**
     * @When another product is created with identifier :identifier
     */
    public function aProductIsCreatedWithIdentifier(string $identifier): void
    {
        $this->updatedProduct = $this->productBuilder->withIdentifier($identifier)->build(false);
    }

    /**
     * @Then the error :errorMessage is raised
     *
     * @throws \Exception
     */
    public function anErrorShouldBeRaisedBecauseOf(string $errorMessage): void
    {
        $violations = $this->productValidator->validate($this->updatedProduct);

        $messages = [];
        $isFoundMessage = false;
        foreach ($violations as $violation) {
            $message = $violation->getMessage();
            $messages[] = $message;
            if ($message === $errorMessage) {
                $isFoundMessage = true;
            }
        }

        if (!$isFoundMessage) {
            throw new \Exception(
                sprintf(
                    'Expected error message "%s" was not found, %s given', $errorMessage,
                    implode(',', $messages)
                )
            );
        }
    }
}

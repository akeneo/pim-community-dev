<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

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
    private Builder\Product $productBuilder;
    private ValidatorInterface $productValidator;
    private InMemoryProductRepository $productRepository;
    private ProductInterface $updatedProduct;

    public function __construct(
        Builder\Product $productBuilder,
        ValidatorInterface $productValidator,
        InMemoryProductRepository $productRepository
    ) {
        $this->productBuilder = $productBuilder;
        $this->productValidator = $productValidator;
        $this->productRepository = $productRepository;
    }

    /**
     * @When /^a(?:nother)? product is created with identifier "([^"]*)"$/
     */
    public function aProductIsCreatedWithIdentifier(string $identifier): void
    {
        $this->updatedProduct = $this->productBuilder->withIdentifier($identifier)->build(false);
    }

    /**
     * @When a product is created with values:
     */
    public function aProductIsCreatedWithValues(TableNode $table): void
    {
        $this->productBuilder->withIdentifier('foo');
        foreach ($table as $row) {
            if (isset($row['json_data']) && '' !== $row['json_data']) {
                $data = \json_decode($row['json_data'], true);
            } else {
                $data = $row['data'];
                if (preg_match('/,/', $data)) {
                    $data = explode(',', $row['data']);
                }
            }

            $this->productBuilder->withValue($row['attribute'], $data, $row['locale'] ?? '', $row['scope'] ?? '');
        }

        $this->updatedProduct = $this->productBuilder->build(false);
    }

    /**
     * @Then :violationCount violation is raised
     * @Then :violationCount violations are raised
     *
     * @throws \Exception
     */
    public function violationsAreRaised(int $violationCount): void
    {
        $violations = $this->productValidator->validate($this->updatedProduct);
        if ($violationCount !== $violations->count()) {
            throw new \Exception(sprintf(
                '%d violation%s expected, %d violation%s found: %s',
                $violationCount,
                $violationCount > 1 ? 's' : '',
                $violations->count(),
                $violations->count() > 1 ? 's' : '',
                $violations->__toString()
            ));
        }
    }

    /**
     * @Then the error :errorMessage is raised
     * @Then the violation :errorMessage is raised
     *
     * @throws \Exception
     */
    public function theErrorIsRaised(string $errorMessage): void
    {
        $violations = $this->productValidator->validate($this->updatedProduct);
        if ($violations->count() === 0) {
            throw new \Exception(
                sprintf('Expected error message "%s" but no violation was found', $errorMessage)
            );
        }

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
                    'Expected error message "%s" was not found, %s given',
                    $errorMessage,
                    implode(PHP_EOL, $messages)
                )
            );
        }
    }

    /**
     * @Then the error :errorMessage is raised on validation
     */
    public function theErrorIsRaisedOnValidation(string $errorMessage): void
    {
        $this->updatedProduct = $this->productRepository->findOneByIdentifier('my_product');

        $this->theErrorIsRaised($errorMessage);
    }

    /**
     * @Then the error :errorMessage is raised at path :errorPath
     * @Then the violation :errorMessage is raised at path :errorPath
     *
     * @throws \Exception
     */
    public function theErrorIsRaisedAtPath(string $errorMessage, string $errorPath): void
    {
        $violations = $this->productValidator->validate($this->updatedProduct);
        if ($violations->count() === 0) {
            throw new \Exception(
                sprintf('Expected violation message "%s" but no violation was found', $errorMessage)
            );
        }

        $messages = [];
        $isFoundMessage = false;

        foreach ($violations as $violation) {
            print_r(get_class($violation));
            $message = $violation->getMessage();
            $path = $violation->getPropertyPath();
            $messages[] = sprintf('path: %s, message: %s', $path, $message);
            if ($message === $errorMessage && $path === $errorPath) {
                $isFoundMessage = true;
            }
        }

        if (!$isFoundMessage) {
            throw new \Exception(
                sprintf(
                    'Expected violation message "%s" at path "%s" was not found, %s given',
                    $errorMessage,
                    $errorPath,
                    implode(PHP_EOL, $messages)
                )
            );
        }
    }

    /**
     * @Then no error is raised
     * @Then no product violation is raised
     */
    public function noErrorIsRaised()
    {
        $violations = $this->productValidator->validate($this->updatedProduct);
        Assert::count($violations, 0, 'No violation should be raised, found: ' . $violations->__toString());
    }

    public function setUpdatedProduct(ProductInterface $updatedProduct): void
    {
        $this->updatedProduct = $updatedProduct;
    }
}

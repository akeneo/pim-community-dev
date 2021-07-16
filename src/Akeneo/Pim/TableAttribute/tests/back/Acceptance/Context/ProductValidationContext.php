<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\Acceptance\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class ProductValidationContext implements Context
{
    private Builder\Product $productBuilder;
    private ValidatorInterface $productValidator;
    private InMemoryProductRepository $productRepository;
    private ProductInterface $updatedProduct;
    private ConstraintViolationListInterface $violations;

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
        $this->violations = $this->productValidator->validate($this->updatedProduct);
    }

    /**
     * @Then :violationCount violation is raised
     * @Then :violationCount violations are raised
     *
     * @throws \Exception
     */
    public function violationsAreRaised(int $violationCount): void
    {
        if ($violationCount !== $this->violations->count()) {
            throw new \Exception(sprintf(
                '%d violation%s expected, %d violation%s found',
                $violationCount,
                $violationCount > 1 ? 's' : '',
                $this->violations->count(),
                $this->violations->count() > 1 ? 's' : '',
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
        if ($this->violations->count() === 0) {
            throw new \Exception(
                sprintf('Expected violation message "%s" but no violation was found', $errorMessage)
            );
        }

        $messages = [];
        $isFoundMessage = false;
        foreach ($this->violations as $violation) {
            $message = $violation->getMessage();
            $messages[] = $message;
            if ($message === $errorMessage) {
                $isFoundMessage = true;
            }
        }

        if (!$isFoundMessage) {
            throw new \Exception(sprintf(
                'Expected violation message "%s" was not found, %s given', $errorMessage,
                implode(',', $messages)
            ));
        }
    }

    /**
     * @Then the error :errorMessage is raised at path :errorPath
     * @Then the violation :errorMessage is raised at path :errorPath
     *
     * @throws \Exception
     */
    public function theErrorIsRaisedAtPath(string $errorMessage, string $errorPath): void
    {
        if ($this->violations->count() === 0) {
            throw new \Exception(
                sprintf('Expected violation message "%s" but no violation was found', $errorMessage)
            );
        }

        $messages = [];
        $isFoundMessage = false;

        foreach ($this->violations as $violation) {
            print_r(get_class($violation));
            $message = $violation->getMessage();
            $path = $violation->getPropertyPath();
            $messages[] = sprintf('path: %s, message: %s', $path , $message);
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
                    implode(',', $messages)
                )
            );
        }
    }

    /**
     * @Then no error is raised
     * @Then no product violation is raised
     */
    public function noErrorIsRaised(): void
    {
        $violations = $this->productValidator->validate($this->updatedProduct);
        Assert::count($violations, 0, 'No violation should be raised, found: ' . $violations->__toString());
    }
}

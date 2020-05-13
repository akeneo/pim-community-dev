<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Acceptance\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class QuantifiedAssociationsContext implements Context
{
    /** @var Product|null */
    private $product;

    /** @var ConstraintViolationListInterface|null */
    private $violations;

    /** @var QuantifiedAssociations|null */
    private $quantifiedAssociations;

    /* --- */

    /** @var ValidatorInterface */
    private $validator;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    public function __construct(
        ValidatorInterface $validator,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver
    ) {
        $this->validator = $validator;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
    }

    /**
     * @Given a product with an invalid quantified association
     */
    public function aProductWithAnInvalidQuantifiedAssociation(): void
    {
        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'foo',
                    ],
                ],
            ],
            'quantified_associations' => [
                'INVALID_ASSOCIATION_TYPE' => [
                    'products' => [],
                    'product_models' => [],
                ],
            ],
        ]);
    }

    /**
     * @When I try to save this product
     */
    public function iTryToSaveThisProduct(): void
    {
        if (null === $this->product) {
            throw new \LogicException('You need to specify a product for the scenario');
        }

        $this->violations = $this->validator->validate($this->product);
//        $this->productSaver->save($this->product);
    }

    /**
     * @Then there is a validation error on this quantified association
     */
    public function thereIsAValidationErrorOnThisQuantifiedAssociation()
    {
        dump($this->violations);
    }

    /**
     * @Given quantified associations with:
     */
    public function givenQuantifiedAssociationsWith(PyStringNode $content)
    {
        $raw = json_decode($content->getRaw(), true);
        QuantifiedAssociations::createWithAssociationsAndMapping(
            $raw
        );
    }

    private function createProduct(array $values): Product
    {
        $product = new Product();
        $this->productUpdater->update(
            $product,
            $values
        );

        return $product;
    }
}

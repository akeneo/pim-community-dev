<?php

declare(strict_types = 1);

namespace Pim\Test\Acceptance\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Factory\AttributeFactory;
use Pim\Test\Common\Persistence\InMemoryAttributeRepository;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Behat context defining step implementations for acceptance tests.
 *
 * TODO: Split it in smaller contexts later.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AcceptanceContext implements Context
{
    /** @var AttributeFactory */
    private $attributeFactory;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ValidatorInterface */
    private $productValidator;

    /** @var ConstraintViolationListInterface|null */
    private $lastProductErrors;

    public function __construct(
        AttributeFactory $attributeFactory,
        InMemoryAttributeRepository $attributeRepository,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $productValidator
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeRepository = $attributeRepository;
        $this->productBuilder = $productBuilder;
        $this->productValidator = $productValidator;
    }

    /**
     * @Given an identifier attribute has been created
     */
    public function loadIdentifierAttribute(): void
    {
        $identifierAttribute = $this->attributeFactory->createAttribute(AttributeTypes::IDENTIFIER);
        $identifierAttribute->setCode('sku');

        $this->attributeRepository->save($identifierAttribute);
    }

    /**
     * @Given a text attribute :attributeCode with a validation rule ":validationRule" has been created
     */
    public function loadTextAttributeWithValidationRule(string $attributeCode, string $validationRule): void
    {
        $attribute = $this->attributeFactory->createAttribute(AttributeTypes::TEXT);
        $attribute->setCode($attributeCode);
        $attribute->setValidationRule($validationRule);

        $this->attributeRepository->save($attribute);
    }

    /**
     * @Given a text attribute :attributeCode with a validation rule "regexp" and a pattern ":pattern" has been created
     */
    public function loadTextAttributeWithRegexValidationRule(string $attributeCode, string $pattern): void
    {
        $attribute = $this->attributeFactory->createAttribute(AttributeTypes::TEXT);
        $attribute->setCode($attributeCode);
        $attribute->setValidationRule('regexp');
        $attribute->setValidationRegexp($pattern);

        $this->attributeRepository->save($attribute);
    }

    /**
     * @When I create a product containing the following values:
     *
     * @param TableNode $values
     */
    public function createAProductContainingTheFollowingValues(TableNode $values): void
    {
        $product = $this->productBuilder->createProduct('new_product');

        foreach ($values->getRows() as [$attributeCode, $value]) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $this->productBuilder->addOrReplaceValue($product, $attribute, null, null, $value);
        }

        $this->lastProductErrors = $this->productValidator->validate($product);
    }

    /**
     * @Then the :code value should be invalid with the message ":message"
     */
    public function theValueShouldBeInvalid(string $code, string $message): void
    {
        if (null === $this->lastProductErrors || count($this->lastProductErrors) === 0) {
            throw new \Exception('No validation error found.');
        }

        foreach ($this->lastProductErrors as $error) {
            if (sprintf('values[%s-<all_channels>-<all_locales>].data', $code) === $error->getPropertyPath()
                && $message === $error->getMessage()
            ) {
                return;
            }
        }

        throw new \Exception(
            sprintf('The value of "%s" should be invalid but is not, or the error message is wrong.', $code)
        );
    }
}

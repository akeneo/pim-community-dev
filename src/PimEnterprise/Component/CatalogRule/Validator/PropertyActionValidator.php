<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validates if the set action field supports the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PropertyActionValidator extends ConstraintValidator
{
    /** @var ActionApplierRegistryInterface */
    protected $applierRegistry;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ActionApplierRegistryInterface $applierRegistry
     * @param ProductBuilderInterface        $productBuilder
     * @param ValidatorInterface             $validator
     * @param AttributeRepositoryInterface   $attributeRepository
     */
    public function __construct(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository = null
    ) {
        $this->applierRegistry = $applierRegistry;
        $this->productBuilder = $productBuilder;
        $this->productValidator = $validator;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        if (!($action instanceof ActionInterface)) {
            throw new \LogicException(sprintf('Action of type "%s" can not be validated.', gettype($action)));
        }

        $fakeProduct = $this->createProduct();
        try {
            $this->applierRegistry->getActionApplier($action)->applyAction($action, [$fakeProduct]);
        } catch (\Exception $e) {
            $this->context->buildViolation(
                $constraint->message,
                ['%message%' => $e->getMessage()]
            )->addViolation();
        }

        $errors = $this->productValidator->validate($fakeProduct);
        $errors = $this->removeIdentifierViolations($errors);

        foreach ($errors as $error) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '%message%' => $error->getMessage(),
                ]
            )->addViolation();
        }
    }

    /**
     * Create a fake product to allow validation
     *
     * @return ProductInterface
     */
    protected function createProduct()
    {
        $product = $this->productBuilder->createProduct('FAKE_SKU_FOR_RULE_VALIDATION_' . microtime());

        return $product;
    }

    /**
     * Remove all violations related to identifier, because the faked identifier could not pass the validation
     * due to custom constraints on it, as a regex on the identifier attribute.
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return ConstraintViolationListInterface
     */
    protected function removeIdentifierViolations(ConstraintViolationListInterface $violations)
    {
        #TODO: Remove this 'if' on master
        if (null !== $this->attributeRepository) {
            $identifierPath = sprintf('values[%s-<all_channels>-<all_locales>]', $this->attributeRepository->getIdentifierCode());
            foreach ($violations as $offset => $violation) {
                if (0 === strpos($violation->getPropertyPath(), $identifierPath)) {
                    $violations->remove($offset);
                }
            }
        }

        return $violations;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ActionInterface as DTOActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\PropertyAction;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Validates if the action field supports the given data.
 * It can validate a model but also a DTO (it tries to denormalize in this case).
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PropertyActionValidator extends ConstraintValidator
{
    protected ActionApplierRegistryInterface $applierRegistry;
    protected ProductBuilderInterface $productBuilder;
    protected ValidatorInterface $productValidator;
    protected AttributeRepositoryInterface $attributeRepository;
    protected DenormalizerInterface $chainedDenormalizer;

    public function __construct(
        ActionApplierRegistryInterface $applierRegistry,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        DenormalizerInterface $chainedDenormalizer
    ) {
        $this->applierRegistry = $applierRegistry;
        $this->productBuilder = $productBuilder;
        $this->productValidator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->chainedDenormalizer = $chainedDenormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, PropertyAction::class);
        if ($action instanceof DTOActionInterface) {
            try {
                $action = $this->chainedDenormalizer->denormalize($action->toArray(), ActionInterface::class);
            } catch (\LogicException $e) {
                return;
            }
        }

        if (!$action instanceof ActionInterface) {
            throw new \LogicException(sprintf(
                'Action of "%s" type can not be validated.',
                is_object($action) ? get_class($action) : gettype($action)
            ));
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
     */
    protected function createProduct(): ProductInterface
    {
        $product = $this->productBuilder->createProduct('FAKE_SKU_FOR_RULE_VALIDATION_' . microtime());

        return $product;
    }

    /**
     * Remove all violations related to identifier, because the faked identifier could not pass the validation
     * due to custom constraints on it, as a regex on the identifier attribute.
     */
    protected function removeIdentifierViolations(
        ConstraintViolationListInterface $violations
    ): ConstraintViolationListInterface {
        $identifierPath = sprintf('values[%s-<all_channels>-<all_locales>]', $this->attributeRepository->getIdentifierCode());
        foreach ($violations as $offset => $violation) {
            if (0 === strpos($violation->getPropertyPath(), $identifierPath)) {
                $violations->remove($offset);
            }
        }

        return $violations;
    }
}

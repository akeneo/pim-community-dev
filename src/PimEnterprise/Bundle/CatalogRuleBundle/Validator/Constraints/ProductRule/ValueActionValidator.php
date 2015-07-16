<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validates if the set action field supports the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ValueActionValidator extends ConstraintValidator
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var PropertyCopierInterface */
    protected $propertyCopier;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ValidatorInterface */
    protected $productValidator;

    /**
     * @param PropertySetterInterface $propertySetter
     * @param PropertyCopierInterface $propertyCopier
     * @param ProductBuilderInterface $productBuilder
     * @param ValidatorInterface      $validator
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        PropertyCopierInterface $propertyCopier,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator
    ) {
        $this->propertySetter = $propertySetter;
        $this->propertyCopier = $propertyCopier;
        $this->productBuilder = $productBuilder;
        $this->productValidator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        if ($action instanceof ProductSetValueActionInterface) {
            $this->validateSetValue($action, $constraint);
        } elseif ($action instanceof ProductCopyValueActionInterface) {
            $this->validateCopyValue($action, $constraint);
        } else {
            throw new \LogicException(sprintf('Action of type "%s" can not be validated.', gettype($action)));
        }
    }

    /**
     * @param ProductSetValueActionInterface $action
     * @param Constraint                     $constraint
     */
    protected function validateSetValue(ProductSetValueActionInterface $action, Constraint $constraint)
    {
        $fakeProduct = $this->createProduct();

        try {
            $this->propertySetter->setData(
                $fakeProduct,
                $action->getField(),
                $action->getValue(),
                ['locale' => $action->getLocale(), 'scope' => $action->getScope()]
            );
        } catch (\Exception $e) {
            $this->context->addViolation(
                $constraint->message,
                ['%message%' => $e->getMessage()]
            );
        }

        $fakeValue = $fakeProduct->getValue($action->getField(), $action->getLocale(), $action->getScope());
        if ($fakeValue) {
            $errors = $this->productValidator->validate($fakeValue);
        }

        foreach ($errors as $error) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '%message%' => $error->getMessage(),
                ]
            );
        }
    }

    /**
     * @param ProductCopyValueActionInterface $action
     * @param Constraint                      $constraint
     */
    protected function validateCopyValue(ProductCopyValueActionInterface $action, Constraint $constraint)
    {
        try {
            $fakeProduct = $this->createProduct();
            $this->propertyCopier->copyData(
                $fakeProduct,
                $fakeProduct,
                $action->getFromField(),
                $action->getToField(),
                [
                    'from_locale' => $action->getFromLocale(),
                    'from_scope'  => $action->getFromScope(),
                    'to_locale'   => $action->getToLocale(),
                    'to_scope'    => $action->getToScope()
                ]
            );
        } catch (\Exception $e) {
            $this->context->addViolation(
                $constraint->message,
                ['%message%' => $e->getMessage()]
            );
        }
    }

    /**
     * Create a fake product to allow validation
     *
     * @return ProductInterface
     */
    protected function createProduct()
    {
        $product = $this->productBuilder->createProduct('FAKE_SKU_FOR_RULE_VALIDATION');

        return $product;
    }
}

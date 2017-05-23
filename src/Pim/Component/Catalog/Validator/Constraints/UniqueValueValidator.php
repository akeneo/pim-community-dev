<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface;
use Pim\Component\Catalog\Validator\UniqueValuesSet;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for unique value constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueValidator extends ConstraintValidator
{
    /** @var ProductUniqueDataRepositoryInterface */
    protected $repository;

    /** @var UniqueValuesSet */
    protected $uniqueValuesSet;

    /**
     * @param ProductUniqueDataRepositoryInterface $repository
     * @param UniqueValuesSet                      $uniqueValueSet
     */
    public function __construct(ProductUniqueDataRepositoryInterface $repository, UniqueValuesSet $uniqueValueSet)
    {
        $this->repository = $repository;
        $this->uniqueValuesSet = $uniqueValueSet;
    }

    /**
     * Validates if the product value exists in database or if we already tried to validate such value for another
     * product to handle bulk updates
     *
     * It means that we make this validator stateful which is a bad news, the good one is we ensure this validation
     * for any processes (other option was to mess the import as we did with previous implementation)
     *
     * Due to constraint guesser, the constraint is applied on ProductValueInterface when applied
     * directly through validator.
     *
     * The constraint guesser should be re-worked in a future version to avoid such behavior
     *
     * @param ProductValueInterface $productValue
     * @param Constraint            $constraint
     *
     * @see Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser
     */
    public function validate($productValue, Constraint $constraint)
    {
        if (empty($productValue)) {
            return;
        }

        if ($productValue instanceof ProductValueInterface && $productValue->getAttribute()->isUnique()) {
            $root = $this->context->getRoot();
            // during the validation of variant groups, $root is not a product but a product value
            // we don't have to check if the value already exists in this case
            $valueAlreadyExists = $root instanceof ProductInterface ? $this->alreadyExists(
                $productValue,
                $root
            ) : false;
            $valueAlreadyProcessed = $root instanceof ProductInterface ? $this->hasAlreadyValidatedTheSameValue(
                $productValue,
                $root
            ) : false;

            if ($valueAlreadyExists || $valueAlreadyProcessed) {
                $valueData = $productValue->__toString();
                $attributeCode = $productValue->getAttribute()->getCode();
                if (null !== $valueData && '' !== $valueData) {
                    $this->context->buildViolation(
                        $constraint->message,
                        ['%value%' => $valueData, '%attribute%' => $attributeCode]
                    )->addViolation();
                }
            }
        }
    }

    /**
     * @param ProductValueInterface $productValue
     * @param ProductInterface      $product
     *
     * @return bool
     */
    protected function alreadyExists(ProductValueInterface $productValue, ProductInterface $product)
    {
        return $this->repository->uniqueDataExistsInAnotherProduct($productValue, $product);
    }

    /**
     * Checks if the same exact value has already been processed on a different product instance
     *
     * @param ProductValueInterface $productValue
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasAlreadyValidatedTheSameValue(ProductValueInterface $productValue, ProductInterface $product)
    {
        return false === $this->uniqueValuesSet->addValue($productValue, $product);
    }
}

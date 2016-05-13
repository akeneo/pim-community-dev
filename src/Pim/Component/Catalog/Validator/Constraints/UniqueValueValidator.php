<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
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
    /** @var ProductRepositoryInterface */
    protected $repository;

    /** @var UniqueValuesSet */
    protected $uniqueValuesSet;

    /**
     * @param ProductRepositoryInterface $repository
     * @param UniqueValuesSet            $uniqueValueSet
     */
    public function __construct(ProductRepositoryInterface $repository, UniqueValuesSet $uniqueValueSet)
    {
        $this->repository      = $repository;
        $this->uniqueValuesSet = $uniqueValueSet;
    }

    /**
     * {@inheritdoc}
     *
     * Validates if the product value exists in database or if we already tried to validate such value for another
     * product to handle bulk updates
     *
     * It means that we make this validator stateful which is a bad news, the good one is we ensure this validation
     * for any processes (other option was to mess the import as we did with previous implementation)
     *
     * Due to constraint guesser, the constraint is applied on ProductValueInterface when applied directly through
     * validator
     *
     * The constraint guesser should be re-worked in a future version to avoid such behavior
     *
     * @see Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser
     */
    public function validate($productValue, Constraint $constraint)
    {
        if ($productValue instanceof ProductValueInterface && $productValue->getAttribute()->isUnique()) {
            $valueAlreadyExists    = $this->alreadyExists($productValue);
            $valueAlreadyProcessed = $this->hasAlreadyValidatedTheSameValue($productValue);

            if ($valueAlreadyExists || $valueAlreadyProcessed) {
                $valueData     = $this->formatData($productValue->getData());
                $attributeCode = $productValue->getAttribute()->getCode();

                if (null !== $valueData && '' !== $valueData) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->setParameter('%value%', $valueData)
                        ->setParameter('%attribute%', $attributeCode)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param ProductValueInterface $productValue
     *
     * @return bool
     */
    protected function alreadyExists(ProductValueInterface $productValue)
    {
        return $this->repository->valueExists($productValue);
    }

    /**
     * Checks if the same exact value has already been processed on a different product instance
     *
     * When validates values for a VariantGroup there is not product related to the value
     *
     * @param ProductValueInterface $productValue
     *
     * @return bool
     */
    protected function hasAlreadyValidatedTheSameValue(ProductValueInterface $productValue)
    {
        if (null !== $productValue->getProduct()) {
            return false === $this->uniqueValuesSet->addValue($productValue);
        }

        return false;
    }

    /**
     * @param mixed $productValueData
     *
     * @return string
     */
    protected function formatData($productValueData)
    {
        return ($productValueData instanceof \DateTime) ?
            $productValueData->format('Y-m-d') :
            (string) $productValueData;
    }
}

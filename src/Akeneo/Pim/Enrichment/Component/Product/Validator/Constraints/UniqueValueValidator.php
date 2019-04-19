<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        ProductUniqueDataRepositoryInterface $repository,
        UniqueValuesSet $uniqueValueSet,
        IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->repository = $repository;
        $this->uniqueValuesSet = $uniqueValueSet;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Validates if the product value exists in database or if we already tried to validate such value for another
     * product to handle bulk updates
     *
     * It means that we make this validator stateful which is a bad news, the good one is we ensure this validation
     * for any processes (other option was to mess the import as we did with previous implementation)
     *
     * Due to constraint guesser, the constraint is applied on ValueInterface when applied
     * directly through validator.
     *
     * The constraint guesser should be re-worked in a future version to avoid such behavior
     *
     * @param ValueInterface $value
     * @param Constraint     $constraint
     *
     * @see \Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UniqueValueGuesser
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueValue) {
            throw new UnexpectedTypeException($constraint, UniqueValue::class);
        }

        if (empty($value) || !$value instanceof ValueInterface) {
            return;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        if (null !== $attribute && $attribute->isUnique()) {
            $root = $this->context->getRoot();
            // during the validation of variant groups, $root is not a product but a product value
            // we don't have to check if the value already exists in this case
            $valueAlreadyExists = $root instanceof ProductInterface ? $this->alreadyExists(
                $value,
                $root
            ) : false;
            $valueAlreadyProcessed = $root instanceof ProductInterface ? $this->hasAlreadyValidatedTheSameValue(
                $value,
                $root
            ) : false;

            if ($valueAlreadyExists || $valueAlreadyProcessed) {
                $valueData = $value->__toString();
                $attributeCode = $value->getAttributeCode();
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
     * @param ValueInterface   $value
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function alreadyExists(ValueInterface $value, ProductInterface $product)
    {
        return $this->repository->uniqueDataExistsInAnotherProduct($value, $product);
    }

    /**
     * Checks if the same exact value has already been processed on a different product instance
     *
     * @param ValueInterface   $value
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasAlreadyValidatedTheSameValue(ValueInterface $value, ProductInterface $product)
    {
        return false === $this->uniqueValuesSet->addValue($value, $product);
    }
}

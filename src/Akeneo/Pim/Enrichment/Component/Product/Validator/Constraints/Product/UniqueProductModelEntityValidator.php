<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Check that another product model does not have the same identifier
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueProductModelEntityValidator extends ConstraintValidator
{
    public function __construct(
        private FindId $findId,
        private UniqueValuesSet $uniqueValuesSet
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProductModelEntity) {
            throw new UnexpectedTypeException($constraint, UniqueProductModelEntity::class);
        }

        if (!$entity instanceof ProductModelInterface) {
            throw new UnexpectedTypeException($constraint, ProductModelInterface::class);
        }

        if (null === $entity->getCode()) {
            return;
        }

        $identifierValue = $this->getIdentifierValue($entity);
        if (false === $this->uniqueValuesSet->addValue($identifierValue, $entity)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();

            return;
        }

        /**
         * Then you check if it has not already been saved in the database
         */
        $idFromDatabase = $this->findId->fromIdentifier($entity->getCode());
        if (null === $idFromDatabase) {
            return;
        }

        /**
         * We don't want to validate a product code if we update a product model because we have already validated the
         * product code during the creation
         */
        if ((string) $entity->getId() !== $idFromDatabase) {
            $this->context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();
        }
    }

    private function getIdentifierValue(ProductModelInterface $entity): ValueInterface
    {
        return ScalarValue::value('code', $entity->getCode());
    }
}

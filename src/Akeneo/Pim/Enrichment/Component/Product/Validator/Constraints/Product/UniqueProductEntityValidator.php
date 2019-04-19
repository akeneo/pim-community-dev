<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Check that another product does not have the same identifier
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueProductEntityValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var UniqueValuesSet */
    private $uniqueValuesSet;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        UniqueValuesSet $uniqueValuesSet,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->uniqueValuesSet = $uniqueValuesSet;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProductEntity) {
            throw new UnexpectedTypeException($constraint, UniqueProductEntity::class);
        }

        if (!$entity instanceof ProductInterface) {
            throw new UnexpectedTypeException($constraint, ProductInterface::class);
        }

        /**
         * We need to check if the product has already been processed during the import. When we apply validation
         * the product may not be saved in the database.
         */
        $identifierValue = $this->getIdentifierValue($entity);
        if (null === $identifierValue) {
            return;
        }

        if (false === $this->uniqueValuesSet->addValue($identifierValue, $entity)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('identifier')
                ->addViolation();

            return;
        }

        /**
         * Then you check if it has not already been saved in the database
         */
        if (null === $entityInDatabase = $this->productRepository->findOneByIdentifier($entity->getIdentifier())) {
            return;
        }

        /**
         * We don't want to validate a product identifier if we update a product because we have already validated the
         * product identifier during the creation
         */
        if ($entity->getId() !== $entityInDatabase->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('identifier')
                ->addViolation();
        }
    }

    private function getIdentifierValue(EntityWithValuesInterface $entity): ?ValueInterface
    {
        $identifier = $this->attributeRepository->getIdentifier();

        if (null === $identifier) {
            return null;
        }

        $identifierCode = $identifier->getCode();

        return $entity->getValues()->getByCodes($identifierCode);
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $productModelRepository)
    {
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProductModelEntity) {
            throw new UnexpectedTypeException($constraint, UniqueProductEntity::class);
        }

        if (!$entity instanceof ProductModelInterface) {
            throw new UnexpectedTypeException($constraint, ProductModelInterface::class);
        }

        /**
         * Then you check if it has not already been saved in the database
         */
        if (null === $entityInDatabase = $this->productModelRepository->findOneByIdentifier($entity->getCode())) {
            return;
        }

        /**
         * We don't want to validate a product code if we update a product model because we have already validated the
         * product code during the creation
         */
        if ($entity->getId() !== $entityInDatabase->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();
        }
    }
}

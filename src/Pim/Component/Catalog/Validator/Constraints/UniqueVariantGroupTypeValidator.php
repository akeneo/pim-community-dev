<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for unique variant group type constraint
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueVariantGroupTypeValidator extends ConstraintValidator
{
    /** @var GroupTypeRepositoryInterface $repository */
    protected $repository;

    /**
     * @param GroupTypeRepositoryInterface $repository
     */
    public function __construct(GroupTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Don't allow creating variant group type if one already exists
     *
     * @param GroupTypeInterface $groupType
     * @param Constraint         $constraint
     */
    public function validate($groupType, Constraint $constraint)
    {
        if ($groupType->isVariant()) {
            $variantGroupType = $this->repository->getVariantGroupType();

            if ($variantGroupType && $variantGroupType->getId() !== $groupType->getId()) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}

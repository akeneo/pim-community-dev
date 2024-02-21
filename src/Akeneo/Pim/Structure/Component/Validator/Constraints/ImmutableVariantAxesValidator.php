<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that the variant axis cannot be modified once set.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImmutableVariantAxesValidator extends ConstraintValidator
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$entity instanceof VariantAttributeSet) {
            throw new UnexpectedTypeException($constraint, VariantAttributeSet::class);
        }

        if (!$constraint instanceof ImmutableVariantAxes) {
            throw new UnexpectedTypeException($constraint, ImmutableVariantAxes::class);
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($entity);
        if (!isset($originalData['axes'])) {
            return;
        }

        $axisCodes = array_map(function (AttributeInterface $axis) {
            return $axis->getCode();
        }, $entity->getAxes()->toArray());

        $originalAxisCodes = array_map(function (AttributeInterface $axis) {
            return $axis->getCode();
        }, $originalData['axes']->toArray());

        if (0 < count($this->getModifiedCodes($axisCodes, $originalAxisCodes))) {
            $this->context->buildViolation(
                ImmutableVariantAxes::IMMUTABLE_VARIANT_AXES,
                [
                    '%level%' => $entity->getLevel(),
                ]
            )->atPath($constraint->propertyPath)->addViolation();
        }
    }

    /**
     * @param array $axisCodes
     * @param array $originalAxisCodes
     *
     * @return array
     */
    private function getModifiedCodes(array $axisCodes, array $originalAxisCodes): array
    {
        return array_merge(
            array_diff($axisCodes, $originalAxisCodes),
            array_diff($originalAxisCodes, $axisCodes)
        );
    }
}

<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Family attribute_as_image validator
 *
 * This validator will check that:
 * - the attribute defined as label is an attribute of the family
 * - the attribute type is defined as parameter
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeAsImageValidator extends ConstraintValidator
{
    /** @var string[] */
    protected $validAttributeTypes;

    /**
     * @param string[] $validAttributeTypes
     */
    public function __construct(array $validAttributeTypes)
    {
        $this->validAttributeTypes = $validAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($family, Constraint $constraint): void
    {
        if (!$constraint instanceof FamilyAttributeAsImage) {
            throw new UnexpectedTypeException($constraint, FamilyAttributeAsImage::class);
        }

        if (!$family instanceof FamilyInterface) {
            return;
        }

        if (null === $family->getAttributeAsImage()) {
            return;
        }

        if (!$this->doesAttributeAsImageBelongToFamily($family)) {
            $this->context
                ->buildViolation($constraint->messageAttribute)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }

        if (!$this->isAttributeAsImageTypeValid($family)) {
            $this->context
                ->buildViolation(sprintf(
                    $constraint->messageAttributeType,
                    join(', ', array_map(function ($validAttributeType) {
                        return sprintf('"%s"', $validAttributeType);
                    }, $this->validAttributeTypes))
                ))
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }

        if (!$this->isAttributeAsImageGlobal($family)) {
            $this->context
                ->buildViolation($constraint->messageAttributeGlobal)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function doesAttributeAsImageBelongToFamily(FamilyInterface $family): bool
    {
        return in_array($family->getAttributeAsImage()->getCode(), $family->getAttributeCodes());
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function isAttributeAsImageTypeValid(FamilyInterface $family): bool
    {
        return in_array($family->getAttributeAsImage()->getType(), $this->validAttributeTypes);
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function isAttributeAsImageGlobal(FamilyInterface $family): bool
    {
        return !$family->getAttributeAsImage()->isScopable() && !$family->getAttributeAsImage()->isLocalizable();
    }
}

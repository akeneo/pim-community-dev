<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NotEmptyVariantAxesValidator extends ConstraintValidator
{
    /** @var EntityWithFamilyVariantAttributesProvider */
    private $axesProvider;

    /**
     * @param EntityWithFamilyVariantAttributesProvider $axesProvider
     */
    public function __construct(EntityWithFamilyVariantAttributesProvider $axesProvider)
    {
        $this->axesProvider = $axesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new UnexpectedTypeException($entity, EntityWithFamilyVariantInterface::class);
        }

        if (!$constraint instanceof NotEmptyVariantAxes) {
            throw new UnexpectedTypeException($constraint, NotEmptyVariantAxes::class);
        }

        if (null === $familyVariant = $entity->getFamilyVariant()) {
            return;
        }

        $axes = $this->axesProvider->getAxes($entity);

        foreach ($axes as $axis) {
            $value = $entity->getValue($axis->getCode());

            if (null === $value || empty($value->getData())) {
                $this->context->buildViolation(
                    NotEmptyVariantAxes::EMPTY_AXIS_VALUE, [
                    '%attribute%' => $axis->getCode()
                ])->addViolation();
            }
        }
    }
}

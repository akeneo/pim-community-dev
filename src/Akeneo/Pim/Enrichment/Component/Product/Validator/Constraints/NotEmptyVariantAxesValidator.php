<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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

        if (null === $entity->getFamilyVariant()) {
            return;
        }

        // This fix prevent the empty variant axes to return a wrong error message when we try to create a sub product
        // model that extends another sub product model. Else the validator thinks it's a variant product (as it will
        // be on the 3 level sub_product_model_2 -> sub_product_model_1 -> root_product_model) and will return the axes
        // on the 3 level.
        if ($entity instanceof ProductModelInterface && null !== $entity->getParent()) {
            if (null !== $entity->getParent()->getParent() ||
                1 === (int) $entity->getParent()->getFamilyVariant()->getNumberOfLevel()) {
                return;
            }
        }

        $axes = $this->axesProvider->getAxes($entity);

        foreach ($axes as $axis) {
            $value = $entity->getValue($axis->getCode());
            $isEmptyMetricValue = (null !== $value && $value->getData() instanceof MetricInterface &&
                null === $value->getData()->getData());

            if (null === $value || null === $value->getData() || '' === $value->getData() || $isEmptyMetricValue) {
                $this->context->buildViolation(NotEmptyVariantAxes::EMPTY_AXIS_VALUE, [
                    '%attribute%' => $axis->getCode()
                ])->atPath($constraint->propertyPath)->addViolation();
            }
        }
    }
}

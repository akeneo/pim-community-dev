<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConversionUnitsValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var MeasureManager */
    protected $measureManager;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param MeasureManager                        $measureManager
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureManager $measureManager
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->measureManager = $measureManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($conversionUnits, Constraint $constraint)
    {
        if (null !== $conversionUnits && is_array($conversionUnits)) {
            foreach ($conversionUnits as $attributeCode => $conversionUnit) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

                if (null === $attribute) {
                    $this->context
                        ->buildViolation($constraint->invalidAttributeCode)
                        ->setParameter('%attributeCode%', $attributeCode)
                        ->addViolation();

                    return;
                }

                if (AttributeTypes::METRIC !== $attribute->getType()) {
                    $this->context
                        ->buildViolation($constraint->notAMetricAttribute)
                        ->setParameter('%attributeCode%', $attributeCode)
                        ->addViolation();

                    return;
                }

                if (!$this->measureManager->unitCodeExistsInFamily(
                        $conversionUnit,
                        $attribute->getMetricFamily()
                    )
                ) {
                    $this->context
                        ->buildViolation($constraint->invalidUnitCode)
                        ->setParameters(
                            [
                                '%unitCode%'      => $conversionUnit,
                                '%attributeCode%' => $attributeCode,
                            ]
                        )
                        ->addViolation();
                }
            }
        }
    }
}

<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks if data is a reference data and if this reference data is configured.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsReferenceDataConfiguredValidator extends ConstraintValidator
{
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var array */
    protected $referenceDataTypes;

    /**
     * @param array                               $referenceDataTypes
     * @param ConfigurationRegistryInterface|null $registry
     */
    public function __construct(array $referenceDataTypes, ConfigurationRegistryInterface $registry = null)
    {
        $this->referenceDataTypes = $referenceDataTypes;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        if (!$constraint instanceof IsReferenceDataConfigured) {
            throw new UnexpectedTypeException($constraint, IsReferenceDataConfigured::class);
        }

        $referenceDataName = $attribute->getReferenceDataName();

        if (null === $referenceDataName || '' === $referenceDataName) {
            return;
        }

        if (null !== $this->registry &&
            in_array($attribute->getType(), $this->referenceDataTypes) &&
            !$this->registry->has($referenceDataName)
        ) {
            $references = array_keys($this->registry->all());

            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%reference_data_name%', $referenceDataName)
                ->setParameter('%references%', implode(', ', $references))
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }
}

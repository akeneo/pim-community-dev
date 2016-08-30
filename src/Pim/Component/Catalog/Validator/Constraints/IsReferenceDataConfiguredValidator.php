<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
    protected $referenceDataType;

    /**
     * @param array                               $referenceDataType
     * @param ConfigurationRegistryInterface|null $registry
     */
    public function __construct(array $referenceDataType, ConfigurationRegistryInterface $registry = null)
    {
        $this->referenceDataType = $referenceDataType;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        $referenceDataName = $attribute->getProperty('reference_data_name');

        if (null !== $this->registry &&
            in_array($attribute->getAttributeType(), $this->referenceDataType) &&
            !$this->registry->has($referenceDataName)
        ) {
            $references = array_keys($this->registry->all());

            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%reference_data_name%', $referenceDataName)
                ->setParameter('%references%', implode(', ', $references))
                ->addViolation();
        }
    }
}

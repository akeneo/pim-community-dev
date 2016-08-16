<?php

namespace Pim\Component\Connector\Validator\Constraints;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the product export builder structure filter about attributes.
 * Attributes filter structure restricts the attribute columns to export.
 *
 * This validator checks if given attributes exist.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterStructureAttributeValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attributes, Constraint $constraint)
    {
        if (null === $attributes || !count($attributes)) {
            return;
        }

        $errorCount = 0;
        foreach ($attributes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null === $attribute) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%attributeCode%', $attributeCode)
                    ->atPath(sprintf('[%d]', $errorCount))
                    ->addViolation();

                $errorCount++;
            }
        }
    }
}

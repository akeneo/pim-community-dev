<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
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

    public function validate($attributes, Constraint $constraint)
    {
        if (null === $attributes) {
            return;
        }

        foreach ($attributes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null === $attribute) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%attributeCode%', $attributeCode)
                    ->addViolation();
            }
        }
    }
}
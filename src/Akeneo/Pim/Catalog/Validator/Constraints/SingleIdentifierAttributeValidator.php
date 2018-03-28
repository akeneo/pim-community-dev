<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for single identifier attribute constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SingleIdentifierAttributeValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface $repository */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Don't allow creating an identifier attribute if one already exists
     *
     * @param AttributeInterface $attribute
     * @param Constraint         $constraint
     */
    public function validate($attribute, Constraint $constraint)
    {
        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            $identifier = $this->attributeRepository->getIdentifier();

            if ($identifier && $identifier->getId() !== $attribute->getId()) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}

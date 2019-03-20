<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
     *
     * @throws \Exception
     */
    public function validate($attribute, Constraint $constraint)
    {
        if (!$constraint instanceof SingleIdentifierAttribute) {
            throw new UnexpectedTypeException($constraint, SingleIdentifierAttribute::class);
        }

        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            $identifier = $this->attributeRepository->getIdentifier();

            if ($identifier && $identifier->getId() !== $attribute->getId()) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}

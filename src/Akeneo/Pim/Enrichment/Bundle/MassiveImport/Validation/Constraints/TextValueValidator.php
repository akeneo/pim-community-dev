<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Validation\Constraints;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value\Value;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\AttributeRepository;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Validator\Constraints\IsString;
use Pim\Component\Catalog\Validator\Constraints\UniqueValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextValueValidator extends ConstraintValidator
{
    /** @staticvar int */
    const TEXT_FIELD_LENGTH = 255;

    /** @var AttributeRepository */
    private $attributeRepository;

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
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Value) {
            throw new UnexpectedTypeException($constraint, Value::class);
        }

        if (!$constraint instanceof TextValue) {
            throw new UnexpectedTypeException($constraint, TextValue::class);
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($value->attributeCode());
        if (AttributeTypes::TEXT !== $attribute->getType()) {
            return;
        }

        $constraints = [new IsString()];

        $characterLimit = null !== $attribute->getMaxCharacters() ? min(static::TEXT_FIELD_LENGTH, $attribute->getMaxCharacters()) : static::TEXT_FIELD_LENGTH;
        $constraints[] = new Assert\Length(['max' => $characterLimit]);

        if ('email' === $attribute->getValidationRule()) {
            $constraints[] = new Assert\Email();
        }

        if ('regexp' === $attribute->getValidationRule() && null !== $attribute->getValidationRegexp()) {
            $constraints[] = new Assert\Email(new Assert\Regex(['pattern' => $attribute->getValidationRegexp()]));
        }

        if (true === $attribute->isRequired()) {
            $constraints[] = new Assert\NotBlank();
        }

        if (true === $attribute->isUnique() && AttributeTypes::IDENTIFIER !== $attribute->getType()) {
            $constraints[] = new UniqueValue();
        }

        foreach ($constraints as $constraint) {
            $violations = $this->context->getValidator()->validate($value->data(), $constraint);
            $this->context->getViolations()->addAll($violations);
        }
    }
}

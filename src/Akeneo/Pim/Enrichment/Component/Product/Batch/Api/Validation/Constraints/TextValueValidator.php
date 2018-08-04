<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Validation\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Product;
use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Value\ProductValue;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\AttributeRepository;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Pim\Component\Catalog\AttributeTypes;
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
        if (!$value instanceof ProductValue) {
            throw new UnexpectedTypeException($constraint, Product::class);
        }

        if (!$constraint instanceof ProductValue) {
            throw new UnexpectedTypeException($constraint, TextValue::class);
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($value->attributeCode());
        if (AttributeTypes::TEXT !== $attribute->getType()) {
            return;
        }

        if ('email' === $attribute->getValidationRule()) {
            $violations = $this->context->getValidator()->validate($value->data(), new Assert\Email());
            foreach ($violations as $violation) {
                $this->context->getViolations()->add($violation);
            }
        }
    }
}

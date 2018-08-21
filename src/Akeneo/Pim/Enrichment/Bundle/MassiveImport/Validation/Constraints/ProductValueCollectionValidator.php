<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Validation\Constraints;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\AttributeTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCollectionValidator extends ConstraintValidator
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
    public function validate($command, Constraint $constraint)
    {
        if (!$command instanceof FillProductValuesCommand) {
            throw new UnexpectedTypeException($constraint, FillProductValuesCommand::class);
        }

        if (!$constraint instanceof ProductValueCollection) {
            throw new UnexpectedTypeException($constraint, ProductValueCollection::class);
        }

        if (null === $command->values()) {
            return;
        }

        foreach ($command->values()->all() as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->attributeCode());

            if (AttributeTypes::TEXT === $attribute->getType()) {
                $violations = $this->context->getValidator()->validate($value, new TextValue());
                $this->context->getViolations()->addAll($violations);
            }
        }
    }
}

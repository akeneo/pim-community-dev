<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Validation\Constraints;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\FillProductValuesCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCollectionValidator extends ConstraintValidator
{
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

        //foreach ($product->values()->all() as $value) {
        //    $violations = $this->context->getValidator()->validate($value);
        //    $this->context->getViolations()->addAll($violations);
        //}
    }
}

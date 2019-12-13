<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OperationShouldBeInstantiableValidator extends ConstraintValidator
{
    /** @var OperationFactory */
    private $operationFactory;

    public function __construct(OperationFactory $operationFactory)
    {
        $this->operationFactory = $operationFactory;
    }

    public function validate($operation, Constraint $constraint)
    {
        if (!$constraint instanceof OperationShouldBeInstantiable) {
            throw new UnexpectedTypeException($constraint, OperationShouldBeInstantiable::class);
        }

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);
        $validator->validate($operation, new Assert\Type('array'));

        try {
            $this->operationFactory->create($operation['type'], $operation['parameters'] ?? []);
        } catch (\InvalidArgumentException | \LogicException $e) {
            $this->context->buildViolation($e->getMessage())->addViolation();
        }
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Przemysław Bogusz <przemyslaw.bogusz@tubotax.pl>
 *
 * TODO This validator is part of Symfony 5.1 and we must remove this class when we will be using Symfony >= 5.1
 * @link https://symfony.com/blog/new-in-symfony-5-1-atleastone-validator
 */
class AtLeastOneOfValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AtLeastOneOf) {
            throw new UnexpectedTypeException($constraint, AtLeastOneOf::class);
        }

        $validator = $this->context->getValidator();

        $messages = [$constraint->message];

        foreach ($constraint->constraints as $key => $item) {
            $violations = $validator->validate($value, $item);

            if (0 === \count($violations)) {
                return;
            }

            if ($constraint->includeInternalMessages) {
                $message = ' ['.($key + 1).'] ';

                if ($item instanceof All || $item instanceof Collection) {
                    $message .= $constraint->messageCollection;
                } else {
                    $message .= $violations->get(0)->getMessage();
                }

                $messages[] = $message;
            }
        }

        $this->context->buildViolation(implode('', $messages))
            ->setCode(AtLeastOneOf::AT_LEAST_ONE_ERROR)
            ->addViolation()
        ;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CustomAction as CustomActionDTO;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CustomAction;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class CustomActionValidator extends ConstraintValidator
{
    private DenormalizerInterface $chainedDenormalizer;

    public function __construct(DenormalizerInterface $chainedDenormalizer)
    {
        $this->chainedDenormalizer = $chainedDenormalizer;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($value, CustomActionDTO::class);
        Assert::isInstanceOf($constraint, CustomAction::class);

        $actionType = $value->type;
        if (null === $actionType || !is_string($actionType)) {
            return;
        }

        $normalizedAction = $value->toArray();
        if (!$this->chainedDenormalizer->supportsDenormalization($normalizedAction, $actionType)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('type')
                ->setInvalidValue($actionType)
                ->addViolation();

            return;
        }

        try {
            $customAction = $this->chainedDenormalizer->denormalize($normalizedAction, $actionType);
        } catch (\Exception $e) {
            // do nothing
            return;
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($customAction);
    }
}

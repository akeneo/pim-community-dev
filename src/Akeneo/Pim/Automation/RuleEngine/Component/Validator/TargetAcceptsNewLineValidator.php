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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ConcatenateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ProductTarget;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\TargetAcceptsNewLine;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class TargetAcceptsNewLineValidator extends ConstraintValidator
{
    protected const ALLOWED_NEW_LINE_TARGET_TYPES = [
        AttributeTypes::TEXTAREA,
    ];

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function validate($action, Constraint $constraint)
    {
        Assert::isInstanceOf($action, ConcatenateAction::class);
        Assert::isInstanceOf($constraint, TargetAcceptsNewLine::class);

        $productSources = $action->from;
        if (!is_array($productSources)) {
            return;
        }

        if (!$action->to instanceof ProductTarget || !is_string($action->to->field)) {
            return;
        }

        $targetAttribute = $this->getAttributes->forCode($action->to->field);
        if (null === $targetAttribute
            ||  in_array($targetAttribute->type(), static::ALLOWED_NEW_LINE_TARGET_TYPES)
        ) {
            return;
        }

        foreach ($action->from as $key => $productSource) {
            if ($productSource->newLine) {
                $this
                    ->context
                    ->buildViolation($constraint->message, ['{{ targetField }}' => $targetAttribute->code()])
                    ->atPath(sprintf('from[%d].new_line', $key))
                    ->addViolation()
                ;
            }
        }
    }
}

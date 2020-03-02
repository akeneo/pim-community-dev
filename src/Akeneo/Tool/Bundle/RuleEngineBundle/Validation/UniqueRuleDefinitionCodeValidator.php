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

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Validation;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class UniqueRuleDefinitionCodeValidator extends ConstraintValidator
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    public function __construct(RuleDefinitionRepositoryInterface $ruleDefinitionRepository)
    {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
    }

    public function validate($ruleDefinition, Constraint $constraint): void
    {
        Assert::isInstanceOf($ruleDefinition, RuleDefinition::class);
        Assert::isInstanceOf($constraint, UniqueRuleDefinitionCode::class);

        $existingRuleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinition->getCode());
        if (null === $existingRuleDefinition) {
            return;
        }

        if ($existingRuleDefinition->getId() !== $ruleDefinition->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}

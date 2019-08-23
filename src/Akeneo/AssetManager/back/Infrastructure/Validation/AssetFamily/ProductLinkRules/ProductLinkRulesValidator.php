<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductLinkRulesValidator extends ConstraintValidator
{
    /** @var RuleEngineValidatorACL */
    private $ruleEngineValidatorACL;

    public function __construct(RuleEngineValidatorACLInterface $ruleEngineValidatorACL)
    {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
    }

    public function validate($productLinkRules, Constraint $constraint): void
    {
        $ruleEngineViolations = new ConstraintViolationList();
        foreach ($productLinkRules as $productLinkRule) {
            $ruleEngineViolations = $this->validateProductSelections($productLinkRule);
            $ruleEngineViolations->addAll($this->validateProductAssignments($productLinkRule));
        }
        $this->addViolationsToContextIfAny($ruleEngineViolations);
    }

    private function validateProductSelections(array $productLinkRule): ConstraintViolationListInterface
    {
        $ruleEngineViolations = new ConstraintViolationList();
        foreach ($productLinkRule['product_selections'] as $productSelection) {
            $ruleEngineViolations = $this->ruleEngineValidatorACL->validateProductSelection($productSelection);
        }

        return $ruleEngineViolations;
    }

    private function validateProductAssignments(array $productLinkRule): ConstraintViolationListInterface
    {
        $ruleEngineViolations = new ConstraintViolationList();
        foreach ($productLinkRule['assign_assets_to'] as $productAssignment) {
            $ruleEngineViolations = $this->ruleEngineValidatorACL->validateProductAssignment($productAssignment);
        }

        return $ruleEngineViolations;
    }

    private function addViolationsToContextIfAny(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
        }
    }
}

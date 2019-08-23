<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

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
        $productSelections = $productLinkRule['product_selections'];
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productSelections, [new NotBlank(['message' => ProductLinkRules::PRODUCT_SELECTION_CANNOT_BE_EMPTY])]);
        foreach ($productSelections as $productSelection) {
            $ruleEngineViolations = $this->ruleEngineValidatorACL->validateProductSelection($productSelection);
        }

        return $ruleEngineViolations;
    }

    private function validateProductAssignments(array $productLinkRule): ConstraintViolationListInterface
    {
        $productAssignments = $productLinkRule['assign_assets_to'];
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productAssignments, [new NotBlank(['message' => ProductLinkRules::PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY])]);
        foreach ($productAssignments as $productAssignment) {
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

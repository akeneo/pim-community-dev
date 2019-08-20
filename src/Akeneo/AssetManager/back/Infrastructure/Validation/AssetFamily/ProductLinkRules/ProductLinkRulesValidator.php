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
            $ruleEngineViolations = $this->validateProductConditions($productLinkRule);
            $ruleEngineViolations->addAll($this->validateProductActions($productLinkRule));
        }
        $this->addViolationsToContextIfAny($ruleEngineViolations);
    }

    private function validateProductConditions(array $productConditions): ConstraintViolationListInterface
    {
        $ruleEngineViolations = new ConstraintViolationList();
        foreach ($productConditions['product_selections'] as $productCondition) {
            $ruleEngineViolations = $this->ruleEngineValidatorACL->validateProductSelection($productCondition);
        }

        return $ruleEngineViolations;
    }

    private function validateProductActions($productActions): ConstraintViolationListInterface
    {
        $ruleEngineViolations = new ConstraintViolationList();
        foreach ($productActions['assign_assets_to'] as $productAction) {
            $ruleEngineViolations = $this->ruleEngineValidatorACL->validateProductAction($productAction);
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

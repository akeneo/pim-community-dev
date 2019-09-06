<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductLinkRulesShouldBeExecutableValidator extends ConstraintValidator
{
    /** @var RuleEngineValidatorACL */
    private $ruleEngineValidatorACL;

    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var ProductSelectionsValidator */
    private $productSelectionValidator;

    /** @var ProductAssignmentsValidator */
    private $productAssignmentsValidator;

    public function __construct(
        RuleEngineValidatorACLInterface $ruleEngineValidatorACL,
        AttributeExistsInterface $attributeExists,
        ProductSelectionsValidator $productSelectionValidator,
        ProductAssignmentsValidator $productAssignmentsValidator
    ) {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->attributeExists = $attributeExists;
        $this->productSelectionValidator = $productSelectionValidator;
        $this->productAssignmentsValidator = $productAssignmentsValidator;
    }

    public function validate($createOrUpdateAssetFamily, Constraint $constraint): void
    {
        foreach ($createOrUpdateAssetFamily->productLinkRules as $productLinkRule) {
            $this->addViolationsToContextIfAny(
                $this->productSelectionValidator->validate(
                    $productLinkRule[RuleTemplate::PRODUCT_SELECTIONS],
                    $createOrUpdateAssetFamily->identifier
                )
            );
            $this->addViolationsToContextIfAny(
                $this->productAssignmentsValidator->validate(
                    $productLinkRule[RuleTemplate::ASSIGN_ASSETS_TO],
                    $createOrUpdateAssetFamily->identifier
                )
            );
        }
    }

    private function addViolationsToContextIfAny(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
        }
    }
}

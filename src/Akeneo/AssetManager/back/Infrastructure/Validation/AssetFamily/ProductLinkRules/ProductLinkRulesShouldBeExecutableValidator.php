<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Infrastructure\Validation\Attribute\MaxFileSize;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

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
        //TODO: to rework ? maybe two separate validators ?
        $assetFamilyIdentifier = null;
        if ($createOrUpdateAssetFamily instanceof CreateAssetFamilyCommand) {
            $assetFamilyIdentifier = $createOrUpdateAssetFamily->code;
        } elseif ($createOrUpdateAssetFamily instanceof EditAssetFamilyCommand) {
            $assetFamilyIdentifier = $createOrUpdateAssetFamily->identifier;
        }

        foreach ($createOrUpdateAssetFamily->productLinkRules as $productLinkRule) {
            $this->addViolationsToContextIfAny(
                $this->productSelectionValidator->validate($productLinkRule[RuleTemplate::PRODUCT_SELECTIONS], $assetFamilyIdentifier)
            );
            $this->addViolationsToContextIfAny(
                $this->productAssignmentsValidator->validate($productLinkRule[RuleTemplate::ASSIGN_ASSETS_TO], $assetFamilyIdentifier)
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

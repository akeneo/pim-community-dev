<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
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

    public function __construct(RuleEngineValidatorACLInterface $ruleEngineValidatorACL, AttributeExistsInterface $attributeExists)
    {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->attributeExists = $attributeExists;
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
        $ruleEngineViolations = new ConstraintViolationList();
        foreach ($createOrUpdateAssetFamily->productLinkRules as $productLinkRule) {
            $ruleEngineViolations->addAll($this->validateProductSelections($productLinkRule, $assetFamilyIdentifier));
            $ruleEngineViolations->addAll($this->validateProductAssignments($productLinkRule));
        }
        $this->addViolationsToContextIfAny($ruleEngineViolations);
    }

    private function validateProductSelections(array $productLinkRule, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        $productSelections = $productLinkRule['product_selections'];
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productSelections, [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_SELECTION_CANNOT_BE_EMPTY])]);
        foreach ($productSelections as $productSelection) {
            $ruleEngineViolations = $this->validateProductSelection($productSelection, $assetFamilyIdentifier);
        }

        return $ruleEngineViolations;
    }

    private function validateProductAssignments(array $productLinkRule): ConstraintViolationListInterface
    {
        $productAssignments = $productLinkRule['assign_assets_to'];
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productAssignments, [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY])]);
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

    private function validateProductSelection(array $productSelection, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        if ($this->hasAnyExtrapolation($productSelection)) {
            return $this->checkExtrapolatedAttributes($productSelection, $assetFamilyIdentifier);
        }

        return $this->ruleEngineValidatorACL->validateProductSelection($productSelection);
    }

    private function hasAnyExtrapolation(array $productSelection): bool
    {
        $isFieldExtrapolated = ReplacePattern::isExtrapolation($productSelection['field']);
        $isValueExtrapolated = ReplacePattern::isExtrapolation($productSelection['value']);
        $isLocaleExtrapolated = isset($productSelection['locale']) ? ReplacePattern::isExtrapolation($productSelection['locale']) : false;
        $isChannelExtrapolated = isset($productSelection['channel']) ? ReplacePattern::isExtrapolation($productSelection['channel']) : false;

        return $isFieldExtrapolated || $isValueExtrapolated || $isLocaleExtrapolated || $isChannelExtrapolated;
    }

    private function checkExtrapolatedAttributes(array $productSelection, string $assetFamilyIdentifier): ConstraintViolationList
    {
        $extrapolatedAttributeCodes = array_merge(
            ReplacePattern::detectPatterns($productSelection['field']),
            ReplacePattern::detectPatterns($productSelection['value'])
        );
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();
        foreach ($extrapolatedAttributeCodes as $extrapolatedAttributeCode) {
            $isAttributeExisting = $this->attributeExists->withAssetFamilyAndCode(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($extrapolatedAttributeCode)
            );
            $violations = $validator->validate($isAttributeExisting,
                new Callback(function ($value, ExecutionContextInterface $context, $payload) use ($extrapolatedAttributeCode) {
                    if (null !== $value && !is_numeric($value)) {
                        $context
                            ->buildViolation(ProductLinkRulesShouldBeExecutable::EXTRAPOLATED_ATTRIBUTE_SHOULD_EXIST, ['%attribute_code%' => $extrapolatedAttributeCode])
                            ->addViolation();
                    }
                })
            );
        }
        return $violations;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetAction;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This class uses the Automation/Rule Engine bounded context validator to determine whether a product link rule is
 * executable by the rule engine. (In some way, this class adapts the validators of the rule engine)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleEngineValidatorACL implements RuleEngineValidatorACLInterface
{
    private const ASSET_CODE_DUMMY = 'VALIDATION_TEST';

    private DenormalizerInterface $actionDenormalizer;

    private ValidatorInterface $productConditionValidator;

    private ValidatorInterface $productAtionValidator;

    public function __construct(
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productConditionValidator,
        ValidatorInterface $productActionValidator
    ) {
        $this->productConditionValidator = $productConditionValidator;
        $this->productAtionValidator = $productActionValidator;
        $this->actionDenormalizer = $actionDenormalizer;
    }

    public function validateProductSelection(array $normalizedProductSelection): ConstraintViolationListInterface
    {
        $normalizedProductSelection['scope'] = $normalizedProductSelection['channel'] ?? null;
        $productCondition = new ProductCondition($normalizedProductSelection);

        return $this->productConditionValidator->validate($productCondition);
    }

    public function validateProductAssignment(array $normalizedProductAssignment): ConstraintViolationListInterface
    {
        $productAction = $this->createProductAction($normalizedProductAssignment);

        $ruleEngineViolations = $this->productAtionValidator->validate($productAction);

        return $this->removeCannotFindAssetViolation($ruleEngineViolations);
    }

    private function createProductAction(array $productAssignment): ActionInterface
    {
        $productAssignment['type'] = $this->getRuleEngineActionType($productAssignment['mode']);
        $productAssignment['field'] = $productAssignment['attribute'];
        $productAssignment['items'] = [self::ASSET_CODE_DUMMY];
        $productAssignment['value'] = [self::ASSET_CODE_DUMMY];
        $productAssignment['scope'] = $productAssignment['channel'] ?? null;
        $productAssignment['locale'] ??= null;

        return $this->actionDenormalizer->denormalize($productAssignment, ActionInterface::class);
    }

    private function getRuleEngineActionType(string $type): string
    {
        if ($type === Action::REPLACE_MODE) {
            return 'set';
        }

        return $type;
    }

    private function removeCannotFindAssetViolation(ConstraintViolationListInterface $ruleEngineViolations): ConstraintViolationListInterface
    {
        $otherViolations = new ConstraintViolationList();
        /** @var ConstraintViolationInterface $ruleEngineViolation */
        foreach ($ruleEngineViolations as $ruleEngineViolation) {
            if ($this->violationHasDummyAssetCode($ruleEngineViolation)) {
                continue;
            }
            $otherViolations->add($ruleEngineViolation);
        }

        return $otherViolations;
    }

    private function violationHasDummyAssetCode(ConstraintViolationInterface $ruleEngineViolation): bool
    {
        $root = $ruleEngineViolation->getRoot();
        if ($root instanceof ProductSetAction) {
            return $this->isDummyAssetCodeViolationWithSetOperation($root);
        }

        if ($root instanceof ProductAddAction) {
            return $this->isDummyAssetCodeViolationWithAddOperation($root);
        }

        return false;
    }

    private function isDummyAssetCodeViolationWithSetOperation(ProductSetAction $root): bool
    {
        $value = $root->getValue();
        if (!is_array($value) || empty($value)) {
            return false;
        }

        return self::ASSET_CODE_DUMMY === current($value);
    }

    private function isDummyAssetCodeViolationWithAddOperation(ProductAddAction $root): bool
    {
        $value = $root->getItems();
        if (!is_array($value) || empty($value)) {
            return false;
        }

        return self::ASSET_CODE_DUMMY === current($value);
    }
}

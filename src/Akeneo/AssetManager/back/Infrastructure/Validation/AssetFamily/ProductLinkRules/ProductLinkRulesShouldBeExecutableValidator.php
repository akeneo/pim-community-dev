<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductLinkRulesShouldBeExecutableValidator extends ConstraintValidator
{
    private RuleEngineValidatorACLInterface $ruleEngineValidatorACL;

    private AttributeExistsInterface $attributeExists;

    private ProductSelectionsValidator $productSelectionValidator;

    private ProductAssignmentsValidator $productAssignmentsValidator;

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
        if ($createOrUpdateAssetFamily->productLinkRules === null) {
            return;
        }

        $violations = $this->validateFormat($createOrUpdateAssetFamily->productLinkRules);
        if (count($violations) > 0) {
            // If the format is invalid no need to go further. We cannot do more complex validation
            // if a key is missing for example.
            return;
        }

        foreach ($createOrUpdateAssetFamily->productLinkRules as $key => $productLinkRule) {
            $this->addViolationsToContextIfAny(
                $this->productSelectionValidator->validate(
                    $productLinkRule[RuleTemplate::PRODUCT_SELECTIONS],
                    $createOrUpdateAssetFamily->identifier
                ),
                sprintf('product_link_rules[%d].%s', $key, RuleTemplate::PRODUCT_SELECTIONS)
            );
            $this->addViolationsToContextIfAny(
                $this->productAssignmentsValidator->validate(
                    $productLinkRule[RuleTemplate::ASSIGN_ASSETS_TO],
                    $createOrUpdateAssetFamily->identifier
                ),
                sprintf('product_link_rules[%d].%s', $key, RuleTemplate::ASSIGN_ASSETS_TO)
            );
        }
    }

    private function addViolationsToContextIfAny(ConstraintViolationListInterface $violations, string $path): void
    {
        foreach ($violations as $violation) {
            $this->context->buildViolation($violation->getMessage())
                ->setParameters($violation->getParameters())
                ->atPath($path)
                ->addViolation();
        }
    }

    private function validateFormat(array $normalizedProductLinkRules): ConstraintViolationListInterface
    {
        $constraints = [
            new Assert\Type('array'),
            new Assert\All([
                new Assert\Collection([
                    RuleTemplate::PRODUCT_SELECTIONS => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Collection([
                                'fields' => [
                                    'field' => new Assert\Type('string'),
                                    'operator' => new Assert\Type('string'),
                                    'value' => new Assert\Type(['string', 'array', 'boolean']),
                                ],
                                'allowMissingFields' => false,
                                'allowExtraFields' => true,
                            ]),
                            new Assert\Collection([
                                'fields' => [
                                    'locale' => new Assert\Type(['null', 'string']),
                                    'channel' => new Assert\Type(['null', 'string']),
                                ],
                                'allowMissingFields' => true,
                                'allowExtraFields' => true,
                            ]),
                        ]),
                    ],
                    RuleTemplate::ASSIGN_ASSETS_TO => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Collection([
                                'fields' => [
                                    'attribute' => [new Assert\Type('string')],
                                    'mode' => [new Assert\Type('string')],
                                ],
                                'allowMissingFields' => false,
                                'allowExtraFields' => true,
                            ]),
                            new Assert\Collection([
                                'fields' => [
                                    'locale' => [new Assert\Type(['null', 'string'])],
                                    'channel' => [new Assert\Type(['null', 'string'])],
                                ],
                                'allowMissingFields' => true,
                                'allowExtraFields' => true,
                            ]),
                        ]),
                    ],
                ]),
            ]),
        ];

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        return $validator
            ->atPath('product_link_rules')
            ->validate($normalizedProductLinkRules, $constraints, Constraint::DEFAULT_GROUP)
            ->getViolations();
    }
}

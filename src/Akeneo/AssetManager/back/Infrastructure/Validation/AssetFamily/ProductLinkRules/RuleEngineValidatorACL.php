<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
    /** @var DenormalizerInterface */
    private $actionDenormalizer;

    /** @var ValidatorInterface */
    private $productConditionValidator;

    /** @var ValidatorInterface */
    private $productAtionValidator;

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

        return $this->productAtionValidator->validate($productAction);
    }

    private function createProductAction(array $productSelection): ActionInterface
    {
        $productSelection['type'] = $this->getRuleEngineActionType($productSelection['mode']);
        $productSelection['field'] = $productSelection['attribute'];
        $productSelection['items'] = ['VALIDATION_TEST'];

        return $this->actionDenormalizer->denormalize($productSelection, ActionInterface::class);
    }

    private function getRuleEngineActionType(string $type): string
    {
        if ($type === Action::REPLACE_MODE) {
            return 'set';
        }

        return $type;
    }
}

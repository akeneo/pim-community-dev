<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

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

    public function validateProductSelection(array $normalizedProductCondition): ConstraintViolationListInterface
    {
        $normalizedProductCondition['scope'] = $normalizedProductCondition['channel'] ?? null;
        $productCondition = new ProductCondition($normalizedProductCondition);

        return $this->productConditionValidator->validate($productCondition);
    }

    public function validateProductAction(array $normalizedProductAction): ConstraintViolationListInterface
    {
        $productAction = $this->createProductAction($normalizedProductAction);

        return $this->productAtionValidator->validate($productAction);
    }

    private function createProductAction(array $productAction): ActionInterface
    {
        $productAction['type'] = $this->getRuleEngineActionType($productAction['mode']);
        $productAction['field'] = $productAction['attribute'];
        $productAction['items'] = ['VALIDATION_TEST'];

        return $this->actionDenormalizer->denormalize($productAction, ActionInterface::class);
    }

    private function getRuleEngineActionType(string $type): string
    {
        if ($type === 'replace') {
            return 'set';
        }

        return $type;
    }
}

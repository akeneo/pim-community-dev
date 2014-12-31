<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Serialize and deserialize a product rule content that is stored in Json.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleContentJsonSerializer implements ProductRuleContentSerializerInterface
{
    /** @var ProductRuleConditionNormalizer */
    protected $conditionNormalizer;

    /** @var ProductSetValueActionNormalizer */
    protected $setValueActionNormalizer;

    /** @var ProductCopyValueActionNormalizer */
    protected $copyValueActionNormalizer;

    /**
     * @param ProductRuleConditionNormalizer   $conditionNormalizer
     * @param ProductSetValueActionNormalizer  $setValueActionNormalizer
     * @param ProductCopyValueActionNormalizer $copyValueActionNormalizer
     */
    public function __construct(
        ProductRuleConditionNormalizer $conditionNormalizer,
        ProductSetValueActionNormalizer $setValueActionNormalizer,
        ProductCopyValueActionNormalizer $copyValueActionNormalizer
    ) {
        $this->conditionNormalizer = $conditionNormalizer;
        $this->setValueActionNormalizer = $setValueActionNormalizer;
        $this->copyValueActionNormalizer = $copyValueActionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(RuleInterface $rule)
    {
        $conditions = $actions = [];
        foreach ($rule->getConditions() as $condition) {
            $conditions[] = $this->conditionNormalizer->normalize($condition);
        }
        foreach ($rule->getActions() as $action) {
            if ($action instanceof ProductSetValueActionInterface) {
                $actions[] = $this->setValueActionNormalizer->normalize($action);
            } elseif ($action instanceof ProductCopyValueActionInterface) {
                $actions[] = $this->copyValueActionNormalizer->normalize($action);
            } else {
                throw new \LogicException(
                    sprintf('Rule "%s" has an unknown type of action "%s".', $rule->getCode(), get_class($action))
                );
            }
        }

        return json_encode([
            'conditions' => $conditions,
            'actions' => $actions,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($content)
    {
        $decodedContent = json_decode($content, true);

        // TODO remove exceptions
        if (!array_key_exists('conditions', $decodedContent)) {
            throw new \LogicException(sprintf('Rule content "%s" should have a "conditions" key.', $content));
        } elseif (!array_key_exists('actions', $decodedContent)) {
            throw new \LogicException(sprintf('Rule content "%s" should have a "actions" key.', $content));
        }

        $conditions = $actions = [];
        foreach ($decodedContent['conditions'] as $condition) {
            // TODO
            $conditions[] = $this->conditionNormalizer->denormalize($condition, 'TODO');
        }
        foreach ($decodedContent['actions'] as $action) {
            if (!isset($action['type'])) {
                throw new \LogicException(sprintf('Rule content "%s" has an action with no type.', $content));
            } elseif (ProductSetValueActionInterface::TYPE === $action['type']) {
                $actions[] = $this->setValueActionNormalizer->denormalize($action, 'TODO');
            } elseif (ProductCopyValueActionInterface::TYPE === $action['type']) {
                $actions[] = $this->copyValueActionNormalizer->denormalize($action, 'TODO');
            } else {
                throw new \LogicException(
                    sprintf('Rule content "%s" has an unknown type of action "%s".', $content, $action['type'])
                );
            }
        }

        return [
            'conditions' => $conditions,
            'actions' => $actions,
        ];
    }
}

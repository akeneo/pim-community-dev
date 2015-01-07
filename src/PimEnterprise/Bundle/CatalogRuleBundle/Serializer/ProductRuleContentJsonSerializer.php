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
 * TODO : if we use array_json doctrine field type, we'll get rid of this class ? if yes we could split Serializer
 * folder to 2 folders Normalizer and Denormalizer to be consistent ? (open question)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleContentJsonSerializer implements ProductRuleContentSerializerInterface
{
    /** @var ProductRuleConditionNormalizer */
    protected $conditionNormalizer;

    /** @var ProductSetValueActionNormalizer */
    protected $setValueNormalizer;

    /** @var ProductCopyValueActionNormalizer */
    protected $copyValueNormalizer;

    /**
     * @param ProductRuleConditionNormalizer   $conditionNormalizer
     * @param ProductSetValueActionNormalizer  $setValueNormalizer
     * @param ProductCopyValueActionNormalizer $copyValueNormalizer
     */
    public function __construct(
        ProductRuleConditionNormalizer $conditionNormalizer,
        ProductSetValueActionNormalizer $setValueNormalizer,
        ProductCopyValueActionNormalizer $copyValueNormalizer
    ) {
        $this->conditionNormalizer = $conditionNormalizer;
        $this->setValueNormalizer = $setValueNormalizer;
        $this->copyValueNormalizer = $copyValueNormalizer;
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
                $actions[] = $this->setValueNormalizer->normalize($action);
            } elseif ($action instanceof ProductCopyValueActionInterface) {
                $actions[] = $this->copyValueNormalizer->normalize($action);
            } else {
                throw new \LogicException(
                    sprintf('Rule "%s" has an unknown type of action "%s".', $rule->getCode(), get_class($action))
                );
            }
            // @TODO and do a switch :)
        }

        // TODO if we use json_array type for the field we get rid of this manual encoding
        return json_encode([
            'conditions' => $conditions,
            'actions' => $actions,
        ]);
    }

    /**
     * TODO: fix this ugly method
     *
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
            // @TODO
            $conditions[] = $this->conditionNormalizer->denormalize($condition, 'TODO');
        }
        foreach ($decodedContent['actions'] as $action) {
            if (!isset($action['type'])) {
                throw new \LogicException(sprintf('Rule content "%s" has an action with no type.', $content));
            } elseif (ProductSetValueActionInterface::TYPE === $action['type']) {
                // @TODO
                $actions[] = $this->setValueNormalizer->denormalize($action, 'TODO');
            } elseif (ProductCopyValueActionInterface::TYPE === $action['type']) {
                // @TODO
                $actions[] = $this->copyValueNormalizer->denormalize($action, 'TODO');
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

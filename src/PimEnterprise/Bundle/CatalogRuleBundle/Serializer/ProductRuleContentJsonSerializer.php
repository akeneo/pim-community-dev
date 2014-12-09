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

    /** @var ProductRuleActionNormalizer */
    protected $actionNormalizer;

    /**
     * @param ProductRuleConditionNormalizer $conditionNormalizer
     * @param ProductRuleActionNormalizer    $actionNormalizer
     */
    public function __construct(ProductRuleConditionNormalizer $conditionNormalizer, ProductRuleActionNormalizer $actionNormalizer)
    {
        $this->conditionNormalizer = $conditionNormalizer;
        $this->actionNormalizer = $actionNormalizer;
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
            $actions[] = $this->actionNormalizer->normalize($action);
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
            // TODO
            $actions[] = $this->conditionNormalizer->denormalize($action, 'TODO');
        }

        return [
            'conditions' => $conditions,
            'actions' => $actions,
        ];
    }
}

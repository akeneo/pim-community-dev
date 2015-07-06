<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule;

use Akeneo\Bundle\RuleEngineBundle\Denormalizer\ChainedDenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize a product rule content.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ContentDenormalizer implements DenormalizerInterface, ChainedDenormalizerAwareInterface
{
    /** @var DenormalizerInterface */
    protected $chainedDenormalizer;

    /** @var string */
    protected $ruleClass;

    /** @var string */
    protected $conditionClass;

    /** @var array */
    protected $actionClasses;

    /**
     * @param string $ruleClass
     */
    /**
     * @param string $ruleClass
     * @param string $conditionClass
     * @param array  $actionClasses, the key of the items if the type of action
     */
    public function __construct($ruleClass, $conditionClass, array $actionClasses)
    {
        $this->ruleClass = $ruleClass;
        $this->conditionClass = $conditionClass;
        $this->actionClasses = $actionClasses;
    }

    /**
     * Denormalizes a rule content.
     *
     * {@inheritdoc}
     *
     * @return ["conditions" => ConditionInterface[], "actions" => ActionInterface[])
     */
    public function denormalize($ruleContent, $class, $format = null, array $context = array())
    {
        $conditions = $actions = [];

        foreach ($ruleContent['conditions'] as $condition) {
            $conditions[] = $this->chainedDenormalizer->denormalize(
                $condition,
                $this->conditionClass,
                $format,
                $context
            );
        }

        foreach ($ruleContent['actions'] as $action) {
            if (!isset($action['type'])) {
                throw new \LogicException(
                    sprintf('Rule content "%s" has an action with no type.', json_encode($ruleContent))
                );
            } elseif (!isset($this->actionClasses[$action['type']])) {
                throw new \LogicException(
                    sprintf(
                        'Rule content "%s" has an unknown type of action "%s".',
                        json_encode($ruleContent),
                        $action['type']
                    )
                );
            } else {
                $actions[] = $this->chainedDenormalizer->denormalize(
                    $action,
                    $this->actionClasses[$action['type']],
                    $format,
                    $context
                );
            }
        }

        return [
            'conditions' => $conditions,
            'actions'    => $actions,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($ruleContent, $type, $format = null)
    {
        return $this->ruleClass === $type &&
            $format === 'rule_content' &&
            isset($ruleContent['conditions']) &&
            is_array($ruleContent['conditions']) &&
            isset($ruleContent['actions']) &&
            is_array($ruleContent['actions'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setChainedDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->chainedDenormalizer = $denormalizer;
    }
}

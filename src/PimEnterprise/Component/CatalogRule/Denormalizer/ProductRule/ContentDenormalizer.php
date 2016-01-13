<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule;

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

    /**
     * @param string $ruleClass
     */
    /**
     * @param string $ruleClass
     * @param string $conditionClass
     */
    public function __construct($ruleClass, $conditionClass)
    {
        $this->ruleClass      = $ruleClass;
        $this->conditionClass = $conditionClass;
    }

    /**
     * Denormalizes a rule content.
     *
     * {@inheritdoc}
     *
     * @return ["conditions" => ConditionInterface[], "actions" => ActionInterface[])
     */
    public function denormalize($ruleContent, $class, $format = null, array $context = [])
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
            } else {
                try {
                    $actions[] = $this->chainedDenormalizer->denormalize(
                        $action,
                        $action['type'],
                        $format,
                        $context
                    );
                } catch (\LogicException $e) {
                    throw new \LogicException(
                        sprintf(
                            'Rule content "%s" has an unknown type of action "%s".',
                            json_encode($ruleContent),
                            $action['type']
                        )
                    );
                }
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

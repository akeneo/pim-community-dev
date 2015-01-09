<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product rules.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ContentNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $conditionNormalizer;

    /** @var NormalizerInterface */
    protected $setActionNormalizer;

    /** @var NormalizerInterface */
    protected $copyActionNormalizer;

    /**
     * @param NormalizerInterface $conditionNormalizer
     * @param NormalizerInterface $setActionNormalizer
     * @param NormalizerInterface $copyActionNormalizer
     */
    public function __construct(
        NormalizerInterface $conditionNormalizer,
        NormalizerInterface $setActionNormalizer,
        NormalizerInterface $copyActionNormalizer
    ) {
        $this->conditionNormalizer  = $conditionNormalizer;
        $this->setActionNormalizer  = $setActionNormalizer;
        $this->copyActionNormalizer = $copyActionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($rule, $format = null, array $context = [])
    {
        $conditions = $this->normalizeConditions($rule, $context, $format);
        $actions    = $this->normalizeActions($rule, $context, $format);

        return ['conditions' => $conditions, 'actions' => $actions];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleInterface && 'product' === $data->getType();
    }

    /**
     * Normalize actions
     *
     * @param RuleInterface $rule
     * @param array         $context
     * @param string        $format
     *
     * @return array
     */
    protected function normalizeActions(RuleInterface $rule, array $context, $format)
    {
        $actions = [];
        /** @var $action RuleInterface */
        foreach ($rule->getActions() as $action) {
            if ($action instanceof ProductCopyValueActionInterface) {
                $action = $this->copyActionNormalizer->normalize($action, $format, $context);
            } elseif ($action instanceof ProductSetValueActionInterface) {
                $action = $this->setActionNormalizer->normalize($action, $format, $context);
            } else {
                throw new \LogicException(
                    sprintf(
                        'Rule "%s" has an unknown type of action.',
                        $rule->getCode()
                    )
                );
            }

            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * Normalize conditions
     *
     * @param RuleInterface $rule
     * @param array         $context
     * @param string        $format
     *
     * @return array
     */
    protected function normalizeConditions(RuleInterface $rule, array $context, $format)
    {
        $conditions = [];
        foreach ($rule->getConditions() as $condition) {
            $conditions[] = $this->conditionNormalizer->normalize($condition, $format, $context);
        }

        return $conditions;
    }
}

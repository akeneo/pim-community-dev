<?php

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductRuleDenormalizer implements DenormalizerInterface
{
    /** @var ProductRuleConditionNormalizer */
    protected $conditionNormalizer;

    /** @var ProductRuleActionNormalizer */
    protected $actionNormalizer;

    /** @var string */
    protected $class;

    /** @var string */
    protected $definitionClass;

    /**
     * @param ProductRuleContentSerializerInterface $contentSerializer
     * @param string                                $class
     * @param string                                $definitionClass
     */
    public function __construct(
        ProductRuleConditionNormalizer $conditionNormalizer,
        ProductRuleActionNormalizer $actionNormalizer,
        $class, $definitionClass)
    {
//        $this->contentSerialzer = $contentSerializer;
        $this->conditionNormalizer = $conditionNormalizer;
        $this->actionNormalizer = $actionNormalizer;
        $this->class = $class;
        $this->definitionClass = $definitionClass;
    }

    /**
     * {@inheritdoc}
     *
     * @return RuleDefinitionInterface
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var RuleInterface $rule */
        $rule = $this->getObject($context);
        $rule->setCode($data['code']);
        $rule->setType('product');

        if (isset($data['priority'])) {
            $rule->setPriority((int) $data['priority']);
        }
        if (isset($data['conditions'])) {
            foreach ($data['conditions'] as $rawCondition) {
                //TODO
                $condition = $this->conditionNormalizer->denormalize($rawCondition, 'TODO');
                $rule->addCondition($condition);
            }

        }
        if (isset($data['actions'])) {
            foreach ($data['actions'] as $rawAction) {
                //TODO
                $action = $this->actionNormalizer->denormalize($rawAction, 'TODO');
                $rule->addAction($action);
            }
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->class === $type;
    }

    /**
     * @param array $context
     *
     * @return RuleDefinitionInterface
     */
    protected function getObject(array $context)
    {
        if (isset($context['object'])) {
            return $context['object'];
        }

        return new $this->class(new $this->definitionClass);
    }
}

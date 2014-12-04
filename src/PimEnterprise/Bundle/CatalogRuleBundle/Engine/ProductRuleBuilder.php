<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Exception\BuilderException;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleBuilder implements BuilderInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $ruleClass;

    /** @var ProductRuleContentSerializerInterface */
    protected $ruleContentSerializer;

    /**
     * @param EventDispatcherInterface              $eventDispatcher
     * @param ValidatorInterface                    $validator
     * @param ProductRuleContentSerializerInterface $ruleContentSerializer
     * @param string                                $ruleClass should implement \PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        ProductRuleContentSerializerInterface $ruleContentSerializer,
        $ruleClass
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->ruleContentSerializer = $ruleContentSerializer;
        $this->ruleClass = $ruleClass;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RuleDefinitionInterface $definition)
    {
        //TODO: change the name of the events PRE_LOAD and POST_LOAD
        $this->eventDispatcher->dispatch(RuleEvents::PRE_LOAD, new RuleEvent($definition));

        /** @var \PimEnterprise\Bundle\RuleEngineBundle\Model\Rule $rule */
        $rule = new $this->ruleClass($definition);

        try {
            $content = $this->ruleContentSerializer->deserialize($definition->getContent());
        } catch (\LogicException $e) {
            throw new BuilderException(
                sprintf('Impossible to build the rule "%s". %s', $definition->getCode(), $e->getMessage())
            );
        }

        $rule->setConditions($content['conditions']);
        $rule->setActions($content['actions']);

        $errors = $this->validator->validate($rule);
        if (count($errors)) {
            throw new BuilderException(
                sprintf('Impossible to build the rule "%s" as it does not appear to be valid.', $definition->getCode())
            );
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_LOAD, new RuleEvent($definition));

        return $rule;
    }
}

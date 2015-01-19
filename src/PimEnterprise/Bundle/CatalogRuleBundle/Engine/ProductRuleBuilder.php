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

use Akeneo\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Exception\BuilderException;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleBuilder implements BuilderInterface
{
    /** @var DenormalizerInterface */
    protected $chainedDenormalizer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $ruleClass;

    /**
     * @param DenormalizerInterface    $chainedDenormalizer
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidatorInterface       $validator
     * @param string                   $ruleClass           should implement
     */
    public function __construct(
        DenormalizerInterface $chainedDenormalizer,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        $ruleClass
    ) {
        $this->chainedDenormalizer = $chainedDenormalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->ruleClass = $ruleClass;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RuleDefinitionInterface $definition)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_BUILD, new RuleEvent($definition));

        $rule = new $this->ruleClass($definition);

        try {
            $content = $this->chainedDenormalizer->denormalize(
                $definition->getContent(),
                $this->ruleClass,
                'rule_content'
            );
        } catch (\LogicException $e) {
            throw new BuilderException(
                sprintf('Impossible to build the rule "%s". %s', $definition->getCode(), $e->getMessage())
            );
        }

        $rule->setConditions($content['conditions']);
        $rule->setActions($content['actions']);

        $violations = $this->validator->validate($rule);

        if (count($violations) > 0) {
            throw new BuilderException(
                sprintf(
                    'Impossible to build the rule "%s" as it does not appear to be valid (%s).',
                    $definition->getCode(),
                    $this->violationsToMessage($violations)
                )
            );
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_BUILD, new RuleEvent($definition));

        return $rule;
    }

    /**
     * @param ConstraintViolationListInterface $violations
     *
     * @return string
     */
    protected function violationsToMessage(ConstraintViolationListInterface $violations)
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = sprintf("%s: %s", $violation->getPropertyPath(), $violation->getMessage());
        }

        return implode(', ', $errors);
    }
}

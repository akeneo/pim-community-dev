<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Connector\Processor\Denormalization;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processes product rules definition
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDefinitionProcessor extends AbstractProcessor
{
    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var string rule class*/
    protected $ruleClass;

    /** @var string */
    protected $class;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param DenormalizerInterface                 $denormalizer
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $detacher
     * @param string                                $ruleDefinitionClass
     * @param string                                $ruleClass
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        $ruleDefinitionClass,
        $ruleClass
    ) {
        parent::__construct($repository);
        $this->denormalizer = $denormalizer;
        $this->validator    = $validator;
        $this->detacher     = $detacher;
        $this->ruleClass    = $ruleClass;
        $this->class        = $ruleDefinitionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $definition = $this->findObject($this->repository, $item);

        $rule = $this->buildRuleFromItemAndDefinition($item, $definition);

        $violations = $this->validator->validate($rule);
        if ($violations->count()) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $this->updateDefinitionFromRule($rule, $definition);
    }

    /**
     * Computes the item data and the rule definition object to build a rule object.
     *
     * @param array                   $item
     * @param RuleDefinitionInterface $definition
     *
     * @return RuleInterface|null
     */
    protected function buildRuleFromItemAndDefinition(array $item, RuleDefinitionInterface $definition = null)
    {
        try {
            $rule = $this->denormalizer
                ->denormalize($item, $this->ruleClass, null, ['definitionObject' => $definition]);
        } catch (\LogicException $e) {
            $this->skipItemWithMessage($item, $e->getMessage());
        }

        return $rule;
    }

    /**
     * Updates (or creates) a rule definition from a rule
     *
     * @param RuleInterface           $rule
     * @param RuleDefinitionInterface $ruleDefinition
     *
     * @return RuleDefinitionInterface
     */
    protected function updateDefinitionFromRule(RuleInterface $rule, RuleDefinitionInterface $ruleDefinition = null)
    {
        if (null === $ruleDefinition) {
            $ruleDefinition = new $this->class();
        }

        $ruleDefinition->setCode($rule->getCode());
        $ruleDefinition->setPriority($rule->getPriority());
        $ruleDefinition->setType($rule->getType());
        $ruleDefinition->setContent($rule->getContent());

        return $ruleDefinition;
    }

    /**
     * Detaches the object from the unit of work
     *
     * Detach an object from the UOW is the responsibility of the writer, but to do so, it should know the
     * skipped items or we should use an explicit persist strategy
     *
     * @param mixed $object
     */
    protected function detachObject($object)
    {
        $this->detacher->detach($object);
    }
}

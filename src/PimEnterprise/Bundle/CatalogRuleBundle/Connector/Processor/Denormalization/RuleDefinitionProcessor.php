<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\AbstractProcessor;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processes product rules definition
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDefinitionProcessor extends AbstractProcessor
{
    /** @var string rule class*/
    protected $ruleClass;

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
        parent::__construct($repository, $denormalizer, $validator, $detacher, $ruleDefinitionClass);

        $this->ruleClass = $ruleClass;
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
}

<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processes product rules definition
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleDefinitionArrayToObjectProcessor extends AbstractImportProcessor
{
    /** @var ProductRuleContentSerializerInterface */
    protected $contentSerializer;

    /** @var string rule class*/
    protected $ruleClass;

    /**
     * @param ReferableEntityRepositoryInterface    $repository
     * @param DenormalizerInterface                 $denormalizer
     * @param ValidatorInterface                    $validator
     * @param string                                $ruleDefinitionClass
     * @param ProductRuleContentSerializerInterface $contentSerializer
     * @param string                                $ruleClass
     */
    public function __construct(
        ReferableEntityRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        $ruleDefinitionClass,
        ProductRuleContentSerializerInterface $contentSerializer,
        $ruleClass
    ) {
        parent::__construct($repository, $denormalizer, $validator, $ruleDefinitionClass);

        $this->contentSerializer = $contentSerializer;
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
            $this->handleConstraintViolationsOnItem($item, $violations);
            // TODO: detach the $definition ?
        }

        return $this->updateDefinitionFromRule($rule, $definition);
    }

    /**
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
            $this->handleExceptionOnItem($item, $e);
        }

        return $rule;
    }

    /**
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

        $content = $this->contentSerializer->serialize($rule);

        $ruleDefinition->setCode($rule->getCode());
        $ruleDefinition->setPriority($rule->getPriority());
        $ruleDefinition->setType($rule->getType());
        $ruleDefinition->setContent($content);

        return $ruleDefinition;
    }
}

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

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processes product rules definition via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleDefinitionProcessor extends AbstractImportProcessor
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
     * @param string                               $ruleClass
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
            $this->handleInvalidItem($item, $violations);
            // TODO: detach the $definition ?
        }

        return $this->updateDefinitionFromRule($definition, $rule);
    }

    /**
     * @param array                   $item
     * @param RuleDefinitionInterface $definition
     *
     * @return RuleInterface
     */
    protected function buildRuleFromItemAndDefinition(array $item, RuleDefinitionInterface $definition)
    {
        return $this->denormalizer->denormalize($item, $this->ruleClass, null, ['definitionObject' => $definition]);
    }

    /**
     * @param RuleDefinitionInterface $definition
     * @param RuleInterface           $rule
     *
     * @return RuleDefinitionInterface
     */
    protected function updateDefinitionFromRule(RuleDefinitionInterface $definition, RuleInterface $rule)
    {
        if (null === $definition) {
            $definition = new $this->class();
        }

        $content = $this->contentSerializer->serialize($rule);

        $definition->setCode($rule->getCode());
        $definition->setPriority($rule->getPriority());
        $definition->setType($rule->getType());
        $definition->setContent($content);

        return $definition;
    }
}

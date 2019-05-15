<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processes product rules definition
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDefinitionProcessor extends AbstractProcessor implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
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

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FileStorerInterface */
    private $fileStorer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        AttributeRepositoryInterface $attributeRepository,
        FileStorerInterface $fileStorer,
        $ruleDefinitionClass,
        $ruleClass
    ) {
        parent::__construct($repository);
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->detacher = $detacher;
        $this->ruleClass = $ruleClass;
        $this->class = $ruleDefinitionClass;
        $this->attributeRepository = $attributeRepository;
        $this->fileStorer = $fileStorer;
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
            $item = $this->storeMedias($item);
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

    /**
     * @param array $item
     *
     * @return array
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileTransferException
     * @throws \Exception
     */
    private function storeMedias(array $item): array
    {
        foreach ($item['actions'] as $key => $action) {
            $actionField = $action['field'] ?? $action['from_field'];
            $attribute = $this->attributeRepository->findOneByIdentifier($actionField);

            if (null !== $attribute &&
                in_array($attribute->getCode(), $this->attributeRepository->findMediaAttributeCodes()) &&
                file_exists($action['value'])) {
                $fileInfo = $this->fileStorer->store(
                    new \SplFileInfo($action['value']),
                    FileStorage::CATALOG_STORAGE_ALIAS
                );

                $item['actions'][$key]['value'] = $fileInfo->getKey();
            }
        }

        return $item;
    }
}

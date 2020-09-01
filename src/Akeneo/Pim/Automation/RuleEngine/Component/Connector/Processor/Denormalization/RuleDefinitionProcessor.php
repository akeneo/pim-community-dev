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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
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

    /** @var RuleDefinitionUpdaterInterface */
    protected $ruleDefinitionUpdater;

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
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        AttributeRepositoryInterface $attributeRepository,
        FileStorerInterface $fileStorer,
        $ruleDefinitionClass,
        $ruleClass
    ) {
        parent::__construct($repository);
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->ruleDefinitionUpdater = $ruleDefinitionUpdater;
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
        $item = $this->storeMedias($item);
        $command = new CreateOrUpdateRuleCommand($item);
        $violations = $this->validator->validate($command, null, ['Default', 'import']);
        if ($violations->count()) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }
        $definition = $this->findObject($this->repository, $item);
        $rule = $this->buildRuleFromItemAndDefinition($item, $definition);

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
        $rule = null;

        try {
            $rule = $this->denormalizer
                ->denormalize($item, $this->ruleClass, null, ['definitionObject' => $definition]);
        } catch (\Exception $e) {
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

        $this->ruleDefinitionUpdater->fromRule($ruleDefinition, $rule);

        return $ruleDefinition;
    }

    /**
     * @param array $item
     *
     * @return array
     * @throws FileRemovalException
     * @throws FileTransferException
     * @throws \Exception
     */
    private function storeMedias(array $item): array
    {
        $mediaAttributeCodes = $this->attributeRepository->findMediaAttributeCodes();
        // This check is performed by validators. It's not the responsibility of this class to make the check.
        if (!array_key_exists('actions', $item)) {
            return $item;
        }

        foreach ($item['actions'] as $key => $action) {
            if (!isset($action['value'])) {
                continue;
            }

            $actionField = $action['field'] ?? $action['from_field'];
            $attribute = $this->attributeRepository->findOneByIdentifier($actionField);

            if (null !== $attribute &&
                in_array($attribute->getCode(), $mediaAttributeCodes) &&
                is_string($action['value']) &&
                file_exists($action['value'])
            ) {
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

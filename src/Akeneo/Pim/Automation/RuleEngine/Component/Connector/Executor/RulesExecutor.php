<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Connector\Executor;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;

/**
 * Execute all the rules to a set of entities with family.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class RulesExecutor implements ItemWriterInterface
{
    /** @var RunnerInterface */
    protected $runner;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleRepository;

    /**
     * @param RunnerInterface                   $runner
     * @param RuleDefinitionRepositoryInterface $ruleRepository
     */
    public function __construct(
        RunnerInterface $runner,
        RuleDefinitionRepositoryInterface $ruleRepository
    ) {
        $this->runner = $runner;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param EntityWithValuesInterface[] $entitiesWithValues
     *
     * {@inheritdoc}
     */
    public function write(array $entitiesWithValues): void
    {
        $entityIds = $this->getEntityIds($entitiesWithValues);

        if (!empty($entityIds)) {
            $ruleDefinitions = $this->ruleRepository->findEnabledOrderedByPriority();

            foreach ($ruleDefinitions as $ruleDefinition) {
                $this->runner->run($ruleDefinition, ['selected_entities_with_values' => $entityIds]);
            }
        }
    }

    /**
     * @param EntityWithValuesInterface[] $entitiesWithValues
     *
     * @return string[]
     */
    private function getEntityIds(array $entitiesWithValues): array
    {
        $entityIds = [];

        foreach ($entitiesWithValues as $entityWithValues) {
            if ($entityWithValues instanceof ProductInterface &&
                null !== $entityWithValues->getId()
            ) {
                $entityIds[] = sprintf(
                    '%s%d',
                    'product_',
                    $entityWithValues->getId()
                );
            } elseif ($entityWithValues instanceof ProductModelInterface &&
                null !== $entityWithValues->getId()
            ) {
                $entityIds[] = sprintf(
                    '%s%d',
                    'product_model_',
                    $entityWithValues->getId()
                );
            }
        }

        return $entityIds;
    }
}

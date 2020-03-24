<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet;

use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Calculation of the count of impacted products by the rules
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ImpactedProductCountTasklet implements TaskletInterface
{
    const CHUNK_SIZE = 300;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleDefinitionRepo;

    /** @var DryRunnerInterface */
    protected $productRuleRunner;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        DryRunnerInterface $productRuleRunner,
        BulkSaverInterface $saver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->ruleDefinitionRepo = $ruleDefinitionRepo;
        $this->productRuleRunner = $productRuleRunner;
        $this->saver = $saver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $ruleIds = $jobParameters->get('ruleIds');
        foreach(array_chunk($ruleIds, self::CHUNK_SIZE) as $ruleIdsChunk) {
            $ruleDefinitions = $this->ruleDefinitionRepo->findBy(['id' => $ruleIdsChunk]);

            foreach ($ruleDefinitions as $ruleDefinition) {
                $ruleSubjectSet = $this->productRuleRunner->dryRun($ruleDefinition);
                $ruleDefinition->setImpactedSubjectCount($ruleSubjectSet->getSubjectsCursor()->count());

                $this->stepExecution->incrementSummaryInfo('rule_calculated');
            }

            $this->saver->saveAll($ruleDefinitions);
            $this->cacheClearer->clear();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }
}

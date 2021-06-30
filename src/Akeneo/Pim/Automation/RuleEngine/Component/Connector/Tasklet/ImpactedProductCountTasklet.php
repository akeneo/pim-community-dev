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
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
/**
 * Calculation of the count of impacted products by the rules
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ImpactedProductCountTasklet implements TaskletInterface, TrackableTaskletInterface
{
    const CHUNK_SIZE = 300;
    protected ?StepExecution $stepExecution = null;
    protected RuleDefinitionRepositoryInterface $ruleDefinitionRepo;
    protected DryRunnerInterface $productRuleRunner;
    protected BulkSaverInterface $saver;
    protected EntityManagerClearerInterface $cacheClearer;
    protected JobStopper $jobStopper;
    private JobRepositoryInterface $jobRepository;
    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        DryRunnerInterface $productRuleRunner,
        BulkSaverInterface $saver,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper
    ) {
        $this->ruleDefinitionRepo = $ruleDefinitionRepo;
        $this->productRuleRunner = $productRuleRunner;
        $this->saver = $saver;
        $this->cacheClearer = $cacheClearer;
        $this->jobRepository = $jobRepository;
        $this->jobStopper = $jobStopper;
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $ruleIds = $jobParameters->get('ruleIds');
        $this->stepExecution->setTotalItems(count($ruleIds));
        foreach (array_chunk($ruleIds, self::CHUNK_SIZE) as $ruleIdsChunk) {
            if ($this->jobStopper->isStopping($this->stepExecution)) {
                $this->jobStopper->stop($this->stepExecution);
                return;
            }
            $ruleDefinitions = $this->ruleDefinitionRepo->findBy(['id' => $ruleIdsChunk]);
            foreach ($ruleDefinitions as $ruleDefinition) {
                try {
                    $ruleSubjectSet = $this->productRuleRunner->dryRun($ruleDefinition);
                    $ruleDefinition->setImpactedSubjectCount($ruleSubjectSet->getSubjectsCursor()->count());
                    $this->stepExecution->incrementSummaryInfo('rule_calculated');
                    $this->stepExecution->incrementProcessedItems();
                } catch (\Exception $e) {
                    $this->stepExecution->addWarning(
                        sprintf(
                            'Invalid rule "%s": could not calculate the impacted product count. Internal error : %s',
                            $ruleDefinition->getCode(),
                            $e->getMessage()
                        ),
                        [],
                        new DataInvalidItem(['rule_code' => $ruleDefinition->getCode()])
                    );
                }
            }
            $this->jobRepository->updateStepExecution($this->stepExecution);
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
    public function isTrackable(): bool
    {
        return true;
    }
}

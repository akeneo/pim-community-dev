<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;


use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluateProductsCriteriaParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\WordIgnoredEvent;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class InitializeEvaluationOfAProductSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var CreateProductsCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var JobLauncherInterface */
    private $queueJobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    )
    {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->queueJobLauncher = $queueJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            WordIgnoredEvent::WORD_IGNORED => 'initializeEvaluation'
        ];
    }

    public function initializeEvaluation(WordIgnoredEvent $event)
    {
        try {
            $this->logger->info(__METHOD__, ['event' => $event]);
            $this->createProductsCriteriaEvaluations->create([$event->getProductId()]);
            $this->scheduleEvaluation([$event->getProductId()->toInt()]);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function scheduleEvaluation(array $productIds): void
    {
        $jobInstance = $this->getJobInstance();

        if (null === $jobInstance) {
            throw new \RuntimeException('Unable to schedule criterion evaluation. Evaluation job instance is not found.');
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if(! $user instanceof UserInterface)
        {
            throw new \RuntimeException('Unable to schedule criterion evaluation. User is not found.');
        }

        $jobParameters = [
            EvaluateProductsCriteriaParameters::PRODUCT_IDS => $productIds,
        ];
        $this->queueJobLauncher->launch($jobInstance, $user, $jobParameters);
    }

    private function getJobInstance(): ?JobInstance
    {
        return $this->jobInstanceRepository->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME);
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluateProductsCriteriaParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\TitleSuggestionIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\WordIgnoredEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
    ) {
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
            WordIgnoredEvent::WORD_IGNORED => 'onIgnoredWord',
            TitleSuggestionIgnoredEvent::TITLE_SUGGESTION_IGNORED => 'onIgnoredTitleSuggestion',
            StorageEvents::POST_SAVE => 'onPostSave',
            StorageEvents::POST_SAVE_ALL => 'onPostSaveAll',
        ];
    }

    public function onIgnoredWord(WordIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteriaAndScheduleEvaluation([$event->getProductId()->toInt()]);
    }

    public function onIgnoredTitleSuggestion(TitleSuggestionIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteriaAndScheduleEvaluation([$event->getProductId()->toInt()]);
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof ProductInterface || $subject->isVariant() === true) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteriaAndScheduleEvaluation([intval($subject->getId())]);
    }

    public function onPostSaveAll(GenericEvent $event): void
    {
        $subjects = $event->getSubject();
        if (! is_array($subjects)) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $productIds = $this->getProductIds($subjects);
        if (empty($productIds)) {
            return;
        }

        $this->initializeCriteriaAndScheduleEvaluation($productIds);
    }

    private function getProductIds($subjects): array
    {
        $productIds = [];
        foreach ($subjects as $subject) {
            if (! $subject instanceof ProductInterface || $subject->isVariant() === true) {
                continue;
            }
            $productIds[] = intval($subject->getId());
        }

        return $productIds;
    }

    private function scheduleEvaluation(array $productIds): void
    {
        $jobInstance = $this->getJobInstance();

        if (null === $jobInstance) {
            throw new \RuntimeException('Unable to schedule criterion evaluation. Evaluation job instance is not found.');
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if (! $user instanceof UserInterface) {
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

    private function initializeCriteriaAndScheduleEvaluation(array $productIds)
    {
        try {
            $this->createProductsCriteriaEvaluations->create(
                array_map(function (int $productId) {
                    return new ProductId($productId);
                }, $productIds)
            );
            $this->scheduleEvaluation($productIds);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class InitializeEvaluationOfAProductSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var ConsolidateAxesRates */
    private $consolidateProductAxisRates;

    /** @var Connection */
    private $db;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateProductAxisRates,
        Connection $db
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->logger = $logger;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->db = $db;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Priority greater than zero to ensure that the evaluation is done prior to the re-indexation of the product in ES
            StorageEvents::POST_SAVE => ['onPostSave', 10],
        ];
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $noEvaluation = (bool) $this->db->executeQuery(
            "SELECT 1 FROM pim_configuration WHERE code = 'dqi_no_evaluation'"
        )->fetchColumn();

        if ($noEvaluation === true) {
            return;
        }

        file_put_contents('/srv/pim/var/debug.txt', "Evaluate product\n", FILE_APPEND);

        $productId = intval($subject->getId());
        $this->initializeCriteria($productId);
        $this->evaluatePendingCriteria->evaluateSynchronousCriteria([$productId]);
        $this->consolidateProductAxisRates->consolidate([$productId]);
    }

    private function initializeCriteria($productId)
    {
        try {
            $this->createProductsCriteriaEvaluations->createAll([new ProductId($productId)]);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Unable to create product criteria evaluation',
                [
                    'error_code' => 'unable_to_create_product_criteria_evaluation',
                    'error_message' => $e->getMessage(),
                ]
            );
        }
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateAfterAttributeGroupActivateHandler
{
    public function __construct(
        private readonly GetEntityIdsImpactedByAttributeGroupActivationQueryInterface $getProductUuidsImpactedByAttributeGroupActivationQuery,
        private readonly GetEntityIdsImpactedByAttributeGroupActivationQueryInterface $getProductModelIdsImpactedByAttributeGroupActivationQuery,
        private readonly MessageBusInterface $messageBus,
        private readonly int $batchSize = 50
    ) {
    }

    public function __invoke(AttributeGroupActivationHasChanged $event): void
    {
        $this->dispatchLaunchProductEvaluationsMessage($event);
        $this->dispatchLaunchProductModelEvaluationsMessage($event);
    }

    private function dispatchLaunchProductEvaluationsMessage(AttributeGroupActivationHasChanged $event): void
    {
        $productUuidsBatch = $this->getProductUuidsImpactedByAttributeGroupActivationQuery->forAttributeGroup(
            new AttributeGroupCode($event->attributeGroupCode),
            $this->batchSize
        );
        foreach ($productUuidsBatch as $productUuidCollection) {
            $message = LaunchProductAndProductModelEvaluationsMessage::forProductsOnly(
                $event->updatedAt,
                $productUuidCollection,
                [],
            );
            $this->messageBus->dispatch($message);
        }
    }

    private function dispatchLaunchProductModelEvaluationsMessage(AttributeGroupActivationHasChanged $event): void
    {
        $productModelIdsBatch = $this->getProductModelIdsImpactedByAttributeGroupActivationQuery->forAttributeGroup(
            new AttributeGroupCode($event->attributeGroupCode),
            $this->batchSize
        );
        foreach ($productModelIdsBatch as $productModelIdsCollection) {
            $message = LaunchProductAndProductModelEvaluationsMessage::forProductModelsOnly(
                $event->updatedAt,
                $productModelIdsCollection,
                [],
            );
            $this->messageBus->dispatch($message);
        }
    }
}

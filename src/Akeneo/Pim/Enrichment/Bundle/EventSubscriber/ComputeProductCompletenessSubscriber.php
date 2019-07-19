<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /** @var SaveProductCompletenesses */
    private $saveProductCompletenesses;

    public function __construct(
        CompletenessCalculatorInterface $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $this->completenessCalculator = $completenessCalculator;
        $this->saveProductCompletenesses = $saveProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['computeProductCompleteness', 320],
        ];
    }

    public function computeProductCompleteness(GenericEvent $event): void
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface) {
            return;
        }

        $completenesses = $this->completenessCalculator->calculate($product);
        $collection = new ProductCompletenessCollection(
            $product->getId(),
            array_map(
                function (CompletenessInterface $completeness): ProductCompleteness {
                    return new ProductCompleteness(
                        $completeness->getChannel()->getCode(),
                        $completeness->getLocale()->getCode(),
                        $completeness->getRequiredCount(),
                        $completeness->getMissingAttributes()->map(
                            function (AttributeInterface $missingAttribute): string {
                                return $missingAttribute->getCode();
                            }
                        )->toArray()
                    );
                },
                $completenesses
            )
        );
        $this->saveProductCompletenesses->save($collection);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ComputeAndPersistPublishedProductCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /** @var SavePublishedProductCompletenesses */
    private $savePublishedProductCompletenesses;

    public function __construct(
        CompletenessCalculatorInterface $completenessCalculator,
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses
    ) {
        $this->completenessCalculator = $completenessCalculator;
        $this->savePublishedProductCompletenesses = $savePublishedProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['computePublishedProductCompleteness', 320],
        ];
    }

    public function computePublishedProductCompleteness(GenericEvent $event): void
    {
        $publishedProduct = $event->getSubject();

        if (!$publishedProduct instanceof PublishedProductInterface) {
            return;
        }

        $completenesses = $this->completenessCalculator->calculate($publishedProduct);

        $collection = new PublishedProductCompletenessCollection(
            $publishedProduct->getId(),
            array_map(
                function (CompletenessInterface $completeness): PublishedProductCompleteness {
                    return new PublishedProductCompleteness(
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
        $this->savePublishedProductCompletenesses->save($collection);
    }
}

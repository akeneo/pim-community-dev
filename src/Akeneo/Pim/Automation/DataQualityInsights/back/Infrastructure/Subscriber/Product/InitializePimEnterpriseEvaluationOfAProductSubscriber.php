<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InitializePimEnterpriseEvaluationOfAProductSubscriber implements EventSubscriberInterface
{
    private InitializeEvaluationOfAProductSubscriber $initializeEvaluationOfAProductSubscriberDecorated;

    public function __construct(InitializeEvaluationOfAProductSubscriber $initializeEvaluationOfAProductSubscriberDecorated) {
        $this->initializeEvaluationOfAProductSubscriberDecorated = $initializeEvaluationOfAProductSubscriberDecorated;
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof ProductInterface || $subject instanceof PublishedProductInterface) {
            return;
        }

        $this->initializeEvaluationOfAProductSubscriberDecorated->onPostSave($event);
    }

    public static function getSubscribedEvents()
    {
        return [
            // Priority greater than zero to ensure that the evaluation is done prior to the re-indexation of the product in ES
            StorageEvents::POST_SAVE => ['onPostSave', 10],
        ];
    }
}

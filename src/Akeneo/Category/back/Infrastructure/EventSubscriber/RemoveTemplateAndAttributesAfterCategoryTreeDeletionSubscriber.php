<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Query\DeleteTemplateAndAttributes;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveTemplateAndAttributesAfterCategoryTreeDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FeatureFlag $enrichedCategoryFeature,
        private readonly DeleteTemplateAndAttributes $deleteTemplateAndAttributes
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'removeTemplateAndAttributes',
        ];
    }

    public function removeTemplateAndAttributes(GenericEvent $event): void
    {
        $category = $event->getSubject();

        if (!$category instanceof Category
            || !$this->enrichedCategoryFeature->isEnabled()
            || !array_key_exists('templateUuid', $event->getArguments())
        ) {
            return;
        }

        $templateUuid = $event->getArguments()['templateUuid'];
        ($this->deleteTemplateAndAttributes)($templateUuid);
    }
}

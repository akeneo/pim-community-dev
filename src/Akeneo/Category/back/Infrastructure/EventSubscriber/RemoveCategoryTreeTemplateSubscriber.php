<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByCategoryIdAndTemplateUuid;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryTreeTemplateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GetCategoryInterface $getCategory,
        private readonly GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree,
        private readonly DeleteCategoryTreeTemplateByCategoryIdAndTemplateUuid $deleteCategoryTreeTemplate,
        private readonly FeatureFlag $enrichedCategoryFeature,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeCategoryTreeTemplate',
        ];
    }

    public function removeCategoryTreeTemplate(GenericEvent $event): void
    {
        $category = $event->getSubject();

        if (!$category instanceof Category || !$this->enrichedCategoryFeature->isEnabled()) {
            return;
        }

        $category = $this->getCategory->byId($category->getId());
        if (!$category->isRoot()) {
            return;
        }

        $template = ($this->getCategoryTemplateByCategoryTree)($category->getId());
        if (!$template) {
            return;
        }

        ($this->deleteCategoryTreeTemplate)($category->getId(), $template->getUuid());
    }
}

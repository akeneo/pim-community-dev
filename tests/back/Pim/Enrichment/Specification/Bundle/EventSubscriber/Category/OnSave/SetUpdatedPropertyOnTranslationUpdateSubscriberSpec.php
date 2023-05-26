<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetUpdatedPropertyOnTranslationUpdateSubscriberSpec extends ObjectBehavior
{
    public function it_is_a_doctrine_event_subscriber()
    {
        $this->shouldImplement(EventSubscriber::class);
    }

    public function it_subscribes_to_pre_update_event()
    {
        $this->getSubscribedEvents()
            ->shouldReturn([
                Events::preUpdate,
                Events::prePersist,
                Events::preRemove,
            ]);
    }

    public function it_only_handles_category_translation(ObjectManager $objectManager): void
    {
        $this->preUpdate(new LifecycleEventArgs(new \stdClass(), $objectManager->getWrappedObject()));
    }

    public function it_sets_the_updated_property_on_a_translation_update(
        ObjectManager $objectManager,
        CategoryInterface $category
    ): void {
        $translation = new CategoryTranslation();
        $translation->setForeignKey($category->getWrappedObject());

        $category->setUpdated(Argument::any())
            ->willReturn($category)
            ->shouldBeCalled();

        $this->preUpdate(new LifecycleEventArgs($translation, $objectManager->getWrappedObject()));
    }

    public function it_sets_the_updated_property_on_a_translation_persist(
        ObjectManager $objectManager,
        CategoryInterface $category
    ): void {
        $translation = new CategoryTranslation();
        $translation->setForeignKey($category->getWrappedObject());

        $category->setUpdated(Argument::any())
            ->willReturn($category)
            ->shouldBeCalled();

        $this->prePersist(new LifecycleEventArgs($translation, $objectManager->getWrappedObject()));
    }

    public function it_sets_the_updated_property_on_a_translation_remove(
        ObjectManager $objectManager,
        CategoryInterface $category
    ): void {
        $translation = new CategoryTranslation();
        $translation->setForeignKey($category->getWrappedObject());

        $category->setUpdated(Argument::any())
            ->willReturn($category)
            ->shouldBeCalled();

        $this->preRemove(new LifecycleEventArgs($translation, $objectManager->getWrappedObject()));
    }
}

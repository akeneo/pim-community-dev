<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;
use Pim\Bundle\CatalogBundle\EventSubscriber\AddParentAProductSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddParentAProductSubscriberSpec extends ObjectBehavior
{
    function let(
        Query\ConvertProductToVariantProduct $convertProductToVariantProduct,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($convertProductToVariantProduct, $entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddParentAProductSubscriber::class);
    }

    function it_is_a_symfony_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_application_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ParentHasBeenAddedToProduct::EVENT_NAME => 'scheduleForUpdate'
        ]);
    }

    function it_schedules_variant_products_for_update(
        $entityManager,
        UnitOfWork $unitOfWork,
        ParentHasBeenAddedToProduct $event,
        VariantProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        ProductModelInterface $productModel,
        Collection $groups,
        Collection $association,
        Collection $completenesses,
        FamilyInterface $family,
        Collection $categories,
        \DateTime $updated,
        \DateTime $created,
        Collection $uniqueData
    ) {
        $event->convertedProduct()->willReturn($variantProduct);

        $variantProduct->getId()->willReturn(64);
        $variantProduct->getValuesForVariation()->willReturn($valueCollection);
        $variantProduct->getParent()->willReturn($productModel);
        $variantProduct->getIdentifier()->willReturn('identifier');
        $variantProduct->getGroups()->willReturn($groups);
        $variantProduct->getAssociations()->willReturn($association);
        $variantProduct->isEnabled()->willReturn(true);
        $variantProduct->getCompletenesses()->willReturn($completenesses);
        $variantProduct->getFamily()->willReturn($family);
        $variantProduct->getCategoriesForVariation()->willReturn($categories);
        $variantProduct->getCreated()->willReturn($created);
        $variantProduct->getUpdated()->willReturn($updated);
        $variantProduct->getUniqueData()->willReturn($uniqueData);
        $productModel->getId()->willReturn(40);

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->registerManaged(
            $variantProduct,
            ['id' => 64],
            [
                'id' => 64,
                'parent' => null,
                'familyVariant' => null,
                'identifier' => 'identifier',
                'groups' => $groups,
                'associations' => $association,
                'enabled' => true,
                'completenesses' => $completenesses,
                'family' => $family,
                'categories' => $categories,
                'created' => $created,
                'updated' => $updated,
                'rawValues' => [],
                'uniqueData' => $uniqueData,
            ]
        )->shouldBeCalled();


        $this->scheduleForUpdate($event)->shouldReturn(null);
    }

    function it_updates_the_product_type_before_flushing_them(
        $entityManager,
        $convertProductToVariantProduct,
        UnitOfWork $unitOfWork,
        LifecycleEventArgs $args,
        ParentHasBeenAddedToProduct $event,
        VariantProductInterface $variantProduct,
        ValueCollectionInterface $valueCollection,
        ProductModelInterface $productModel,
        Collection $groups,
        Collection $association,
        Collection $completenesses,
        FamilyInterface $family,
        Collection $categories,
        \DateTime $updated,
        \DateTime $created,
        Collection $uniqueData
    ) {
        $event->convertedProduct()->willReturn($variantProduct);

        $variantProduct->getId()->willReturn(64);
        $variantProduct->getValuesForVariation()->willReturn($valueCollection);
        $variantProduct->getParent()->willReturn($productModel);
        $variantProduct->getIdentifier()->willReturn('identifier');
        $variantProduct->getGroups()->willReturn($groups);
        $variantProduct->getAssociations()->willReturn($association);
        $variantProduct->isEnabled()->willReturn(true);
        $variantProduct->getCompletenesses()->willReturn($completenesses);
        $variantProduct->getFamily()->willReturn($family);
        $variantProduct->getCategoriesForVariation()->willReturn($categories);
        $variantProduct->getCreated()->willReturn($created);
        $variantProduct->getUpdated()->willReturn($updated);
        $variantProduct->getUniqueData()->willReturn($uniqueData);
        $productModel->getId()->willReturn(40);


        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $this->scheduleForUpdate($event);

        $args->getObject()->willReturn($variantProduct);

        $convertProductToVariantProduct->execute($variantProduct)->shouldBeCalled();

        $this->preUpdate($args)->shouldReturn(null);
    }
}

<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Model\Product;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;

class SetProductNormalizedDataSubscriberSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     */
    function it_subscribes_to_preUpdate_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist', 'preUpdate']);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     */
    function it_sets_product_normalize_data_before_inserting_document(
        LifecycleEventArgs $args,
        Product $product,
        NormalizerInterface $normalizer,
        DocumentManager $dm,
        ClassMetadata $metadata,
        UnitOfWork $uow
    ) {
        $args->getDocument()->willReturn($product);
        $normalizer->normalize($product, 'mongodb_json')->willReturn('normalized product');

        $product->setNormalizedData('normalized product')->shouldBeCalled();

        $this->prePersist($args);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     */
    function it_does_nothing_before_insert_when_document_is_not_a_product(
        LifecycleEventArgs $args,
        NormalizerInterface $normalizer
    ) {
        $args->getDocument()->willReturn(null);
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->prePersist($args);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     */
    function it_sets_product_normalize_data_before_updating_document(
        LifecycleEventArgs $args,
        Product $product,
        NormalizerInterface $normalizer,
        DocumentManager $dm,
        ClassMetadata $metadata,
        UnitOfWork $uow
    ) {
        $args->getDocument()->willReturn($product);
        $normalizer->normalize($product, 'mongodb_json')->willReturn('normalized product');

        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($metadata);
        $dm->getUnitOfWork()->willReturn($uow);

        $product->setNormalizedData('normalized product')->shouldBeCalled();
        $uow->recomputeSingleDocumentChangeSet($metadata, $product)->shouldBeCalled();

        $this->preUpdate($args);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     */
    function it_does_nothing_before_update_when_document_is_not_a_product(
        LifecycleEventArgs $args,
        NormalizerInterface $normalizer
    ) {
        $args->getDocument()->willReturn(null);
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->preUpdate($args);
    }
}

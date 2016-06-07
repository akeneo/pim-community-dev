<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;

class ExcludeDeletedAttributeSubscriberSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('pim_catalog_attribute');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_excludes_unexistant_attributes(
        $attributeRepository,
        ProductDraftInterface $productDraft,
        EntityManager $entityManager,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $nameAttribute,
        LifecycleEventArgs $args
    ) {
        $dbData = [
            'values'          => [
                'name'        => ['data' => 'Pipoux', 'locale' => null, 'scope' => null],
                'description' => ['data' => 'undefined', 'locale' => null, 'scope' => null],
            ],
            'review_statuses' => [
                'name'        => ['status' => 'draft', 'locale' => null, 'scope' => null],
                'description' => ['status' => 'draft', 'locale' => null, 'scope' => null],
            ]
        ];

        $args->getObject()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn($dbData);

        $args->getObjectManager()->willReturn($entityManager);
        $entityManager->getRepository('pim_catalog_attribute')->willReturn($attributeRepository);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn(null);

        unset($dbData['values']['description'], $dbData['review_statuses']['description']);
        $productDraft->setChanges($dbData)->shouldBeCalled();

        $this->postLoad($args);
    }
}

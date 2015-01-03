<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Saver;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Saver\ProductSavingOptionsResolver;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductDraftSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet

    ) {
        $this->beConstructedWith(
            $objectManager,
            $optionsResolver,
            $securityContext,
            $factory,
            $repository,
            $dispatcher,
            $collector,
            $changeSet,
            AkeneoStorageUtilsExtension::DOCTRINE_ORM
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\Persistence\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType('Akeneo\Component\Persistence\BulkSaverInterface');
    }
}
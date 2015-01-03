<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Saver\ProductSaver;
use Pim\Bundle\CatalogBundle\Saver\ProductSavingOptionsResolver;
use PimEnterprise\Bundle\WorkflowBundle\Saver\ProductDraftSaver;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DelegatingProductSaverSpec extends ObjectBehavior
{
    function let(
        ProductSaver $workingCopySaver,
        ProductDraftSaver $draftSaver,
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        SecurityContextInterface $securityContext
    ) {
        $this->beConstructedWith(
            $workingCopySaver,
            $draftSaver,
            $objectManager,
            $optionsResolver,
            $securityContext
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
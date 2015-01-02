<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use Akeneo\Component\Persistence\BulkSaverInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes;

class ProductMassEditOperatorSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade,
        BulkSaverInterface $productSaver,
        EditCommonAttributes $commonAttributesOperation
    ) {
        $this->beConstructedWith($securityFacade, $productSaver);
        $this->registerMassEditAction('edit-common-attributes', $commonAttributesOperation);
        $this->setOperationAlias('edit-common-attributes');
    }

    function it_saves_products_when_finalize_operation(
        ProductInterface $product,
        $productSaver,
        $commonAttributesOperation
    ) {
        $commonAttributesOperation->getObjectsToMassEdit()->willReturn([$product]);
        $commonAttributesOperation->getSavingOptions()->willReturn(
            [
                'recalculate' => false,
                'flush'       => true,
                'schedule'    => true
            ]
        );

        $productSaver->saveAll(
            [$product],
            [
                'recalculate' => false,
                'flush'       => true,
                'schedule'    => true
            ]
        )->shouldBeCalled();

        $this->finalizeOperation();
    }
}

<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes;

class ProductMassEditOperatorSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade,
        EditCommonAttributes $commonAttributesOperation
    ) {
        $this->beConstructedWith($securityFacade);
        $this->registerMassEditAction('edit-common-attributes', $commonAttributesOperation);
        $this->setOperationAlias('edit-common-attributes');
    }

    function it_finalizes_operation($commonAttributesOperation)
    {
        $commonAttributesOperation->finalize()->shouldBeCalled();

        $this->finalizeOperation();
    }
}

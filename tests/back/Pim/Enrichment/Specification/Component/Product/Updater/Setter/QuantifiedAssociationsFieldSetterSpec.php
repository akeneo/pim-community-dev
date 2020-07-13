<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\QuantifiedAssociationsFieldSetter;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsFieldSetterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationsFieldSetter::class);
    }

    public function it_is_a_field_setter()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    public function it_only_work_with_quantified_associations_field()
    {
        $this->supportsField('quantified_associations')->shouldReturn(true);
        $this->supportsField('family')->shouldReturn(false);
    }

    public function it_override_quantified_associations_to_a_product(
        ProductInterface $product
    ) {
        $submittedQuantifiedAssociations = [
            'PRODUCTSET_A' => [
                'products' => [
                    ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                ],
            ],
        ];

        $product->patchQuantifiedAssociations($submittedQuantifiedAssociations)->shouldBeCalled();

        $this->setFieldData($product, 'quantified_associations', $submittedQuantifiedAssociations);
    }
}

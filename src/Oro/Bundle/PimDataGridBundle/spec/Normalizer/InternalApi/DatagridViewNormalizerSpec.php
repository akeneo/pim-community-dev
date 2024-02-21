<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer\InternalApi;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Akeneo\UserManagement\Component\Model\UserInterface;

class DatagridViewNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_json_format(DatagridView $view)
    {
        $this->supportsNormalization($view, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($view, 'structured')->shouldReturn(false);
    }

    function it_supports_datagrid_view(DatagridView $view)
    {
        $this->supportsNormalization($view, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
    }

    function it_normalizes_a_datagrid_view(DatagridView $view, UserInterface $user)
    {
        $user->getId()->willReturn(666);

        $view->getId()->willReturn(42);
        $view->getOwner()->willReturn($user);
        $view->getLabel()->willReturn('Cameras');
        $view->getType()->willReturn('public');
        $view->getDatagridAlias()->willReturn('product-grid');
        $view->getColumns()->willReturn(['sku', 'name', 'brand']);
        $view->getFilters()->willReturn('i=1&p=10&s%5Bupdated%5D=1&f%5Bfamily%5D%5Bvalue%5D%5B%5D=mugs');
        $view->getOrder()->willReturn('sku,name,brand');

        $this->normalize($view, 'standard')->shouldReturn([
            'id'             => 42,
            'owner_id'       => 666,
            'label'          => 'Cameras',
            'type'           => 'public',
            'datagrid_alias' => 'product-grid',
            'columns'        => ['sku', 'name', 'brand'],
            'filters'        => 'i=1&p=10&s%5Bupdated%5D=1&f%5Bfamily%5D%5Bvalue%5D%5B%5D=mugs',
        ]);
    }
}

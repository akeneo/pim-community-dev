<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class GroupSavingOptionsResolverSpec extends ObjectBehavior
{
    function it_a_saving_options_resolver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface');
    }

    function it_resolves_single_save_options(GroupInterface $added, GroupInterface $removed)
    {
        $this
            ->resolveSaveOptions(
                ['flush' => false, 'copy_values_to_products' => true, 'add_products' => [$added], 'remove_products' => [$removed]]
            )
            ->shouldReturn(
                ['flush' => false, 'copy_values_to_products' => true, 'add_products' => [$added], 'remove_products' => [$removed]]
            )
        ;
    }

    function it_resolves_default_values_for_single_save_options()
    {
        $this
            ->resolveSaveOptions([])
            ->shouldReturn(
                ['flush' => true, 'copy_values_to_products' => false, 'add_products' => [], 'remove_products' => []]
            )
        ;
    }

    function it_resolves_bulk_save_options(GroupInterface $added, GroupInterface $removed)
    {
        $this
            ->resolveSaveOptions(
                ['flush' => false, 'copy_values_to_products' => true, 'add_products' => [$added], 'remove_products' => [$removed]]
            )
            ->shouldReturn(
                ['flush' => false, 'copy_values_to_products' => true, 'add_products' => [$added], 'remove_products' => [$removed]]
            )
        ;
    }

    function it_resolves_default_values_for_bulk_save_options()
    {
        $this
            ->resolveSaveOptions([])
            ->shouldReturn(
                ['flush' => true, 'copy_values_to_products' => false, 'add_products' => [], 'remove_products' => []]
            )
        ;
    }

    function it_throws_an_exception_when_resolve_unknown_saving_option()
    {
        $this
            ->shouldThrow(new UndefinedOptionsException('The option "fake_option" does not exist. Defined options are: "add_products", "copy_values_to_products", "flush", "remove_products".'))
            ->duringResolveSaveOptions(['fake_option' => true, 'copy_values_to_products' => true]);
    }
}

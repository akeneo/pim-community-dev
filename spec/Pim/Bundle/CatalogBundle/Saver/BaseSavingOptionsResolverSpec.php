<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class BaseSavingOptionsResolverSpec extends ObjectBehavior
{
    function it_resolves_single_save_options() {
        $this
            ->resolveSaveOptions(['flush' => true, 'flush_only_object' => true])
            ->shouldReturn(['flush' => true, 'flush_only_object' => true]);
    }

    function it_resolves_default_values_for_single_save_options() {
        $this
            ->resolveSaveOptions([])
            ->shouldReturn(['flush' => true, 'flush_only_object' => false]);
    }

    function it_resolves_bulk_save_options() {
        $this
            ->resolveSaveAllOptions(['flush' => true])
            ->shouldReturn(['flush' => true]);
    }

    function it_resolves_default_values_for_bulk_save_options() {
        $this
            ->resolveSaveAllOptions([])
            ->shouldReturn(['flush' => true]);
    }

    function it_throws_an_exception_when_resolve_unknown_saving_option() {
        $this
            ->shouldThrow(new InvalidOptionsException('The option "fake_option" does not exist. Known options are: "flush", "flush_only_object"'))
            ->duringResolveSaveOptions(['fake_option' => true, 'flush' => false]);
    }
}

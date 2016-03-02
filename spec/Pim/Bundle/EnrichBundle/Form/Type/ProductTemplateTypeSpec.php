<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTemplateTypeSpec extends ObjectBehavior
{
    function let(
        ProductFormViewInterface $formView,
        TransformProductTemplateValuesSubscriber $subscriber,
        UserContext $userContext,
        ChannelManager $channelManager
    ) {
        $this->beConstructedWith($formView, $subscriber, $userContext, $channelManager, 'ProductTemplate');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\ProductTemplateType');
    }

    function it_is_a_form_type()
    {
        $this->shouldImplement('Symfony\Component\Form\FormTypeInterface');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_product_template');
    }

    function it_has_a_default_configuration(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'    => 'ProductTemplate',
                'currentLocale' => null,
            ]
        )->shouldBeCalled();

        $this->setDefaultOptions($resolver);
    }

    function it_adds_values_to_the_form($subscriber, FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'values',
                'pim_enrich_localized_collection',
                array(
                    'type'               => 'pim_product_value',
                    'allow_add'          => false,
                    'allow_delete'       => false,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                    'currentLocale'      => 'en_GB',
                )
            )
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder->addEventSubscriber($subscriber)->shouldBeCalled();

        $this->buildForm($builder, ['currentLocale' => 'en_GB']);
    }
}

<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryTypeSpec extends ObjectBehavior
{
    function let(FormBuilderInterface $builder)
    {
        $builder->add(Argument::cetera())->willReturn($builder);
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);

        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            'Pim\Bundle\CatalogBundle\Entity\CategoryTranslation'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\CategoryType');
    }

    function it_is_a_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_category');
    }

    function it_builds_the_category_form($builder)
    {
        $builder->add('code')->shouldBeCalled();
        $builder->add(
            'label',
            'pim_translatable_field',
            Argument::type('array')
        )->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_adds_a_disable_field_subscriber($builder)
    {
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'))
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => 'Pim\Bundle\CatalogBundle\Entity\Category'
            ]
        )->shouldBeCalled();

        $this->setDefaultOptions($resolver);
    }

    function it_adds_registered_event_subscribers($builder, EventSubscriberInterface $subscriber)
    {
        $this->addEventSubscriber($subscriber);
        $builder->addEventSubscriber($subscriber)
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }
}

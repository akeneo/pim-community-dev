<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslation;
use Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryTypeSpec extends ObjectBehavior
{
    function let(FormBuilderInterface $builder)
    {
        $builder->add(Argument::cetera())->willReturn($builder);
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);

        $this->beConstructedWith(
            Category::class,
            CategoryTranslation::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryType::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldHaveType(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_category');
    }

    function it_builds_the_category_form($builder)
    {
        $builder->add('code')->shouldBeCalled();
        $builder->add(
            'label',
            TranslatableFieldType::class,
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
                'data_class'  => Category::class
            ]
        )->shouldBeCalled();

        $this->configureOptions($resolver);
    }

    function it_adds_registered_event_subscribers($builder, EventSubscriberInterface $subscriber)
    {
        $this->addEventSubscriber($subscriber);
        $builder->addEventSubscriber($subscriber)
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }
}

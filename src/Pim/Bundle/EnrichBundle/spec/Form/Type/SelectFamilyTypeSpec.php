<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;

class SelectFamilyTypeSpec extends ObjectBehavior
{
    function let(FormBuilderInterface $builder, RouterInterface $router, FamilyRepositoryInterface $familyRepository)
    {
        $builder->add(Argument::cetera())->willReturn($builder);
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);

        $this->beConstructedWith(
            $router
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\SelectFamilyType');
    }

    function it_is_a_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('select_family_type');
    }

    function it_has_a_parent()
    {
        $this->getParent()->shouldReturn('pim_async_select');
    }

    function it_resets_the_view_transformer($builder)
    {
        $builder->resetViewTransformers()->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_builds_the_view(
        FormView $formView,
        FormInterface $form,
        FamilyRepositoryInterface $familyRepository,
        FamilyInterface $familyMugs,
        FamilyInterface $familyWebcams
    ) {
        $options['repository'] = $familyRepository;
        $options['multiple'] = true;
        $form->getData()->willReturn('mugs,webcams');
        $familyRepository->findBy(["code" => ["mugs", "webcams"]])->willReturn([$familyMugs, $familyWebcams]);

        $familyMugs->getLabel()->willReturn('Mugs');
        $familyWebcams->getLabel()->willReturn('Webcams');
        $familyMugs->getCode()->willReturn('mugs');
        $familyWebcams->getCode()->willReturn('webcams');

        $this->buildView($formView, $form, $options);
    }
}

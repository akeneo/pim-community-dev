<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\Twig\ViewElementExtension;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementInterface;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ViewElementExtensionSpec extends ObjectBehavior
{
    function let(
        ViewElementRegistry $registry,
        EngineInterface $templating,
        ViewElementInterface $buttonOne,
        ViewElementInterface $buttonTwo,
        ViewElementInterface $buttonThree
    ) {
        $this->beConstructedWith($registry, $templating);

        $registry->get('button')->willReturn([$buttonOne, $buttonTwo, $buttonThree]);

        $buttonOne->isVisible(Argument::any())->willReturn(true);
        $buttonTwo->isVisible(Argument::any())->willReturn(true);
        $buttonThree->isVisible(Argument::any())->willReturn(true);

        $buttonOne->getAlias()->willReturn('first button');
        $buttonTwo->getAlias()->willReturn('second button');
        $buttonThree->getAlias()->willReturn('third button');

        $buttonOne->getTemplate()->willReturn('button_one.html.twig');
        $buttonTwo->getTemplate()->willReturn('button_two.html.twig');
        $buttonThree->getTemplate()->willReturn('button_three.html.twig');

        $buttonOne->getParameters(Argument::type('array'))->willReturn([]);
        $buttonTwo->getParameters(Argument::type('array'))->willReturn([]);
        $buttonThree->getParameters(Argument::type('array'))->willReturn([]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ViewElementExtension::class);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_registers_view_element_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions->shouldHaveTwigMethod('view_elements', 'renderViewElements', true, ['html']);
        $functions->shouldHaveTwigMethod('view_element_aliases', 'getViewElementAliases', true, ['html']);
    }

    function it_renders_view_elements_of_the_requested_type($registry, $templating, $buttonOne)
    {
        $registry->get('button')->willReturn([$buttonOne]);

        $templating
            ->render('button_one.html.twig', Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('<button id="first_button">First button</button>');

        $this->renderViewElements([], 'button')->shouldReturn('<button id="first_button">First button</button>');
    }

    function it_renders_only_visible_view_elements($registry, $templating, $buttonOne, $buttonTwo)
    {
        $buttonOne->isVisible(Argument::any())->willReturn(false);

        $registry->get('button')->willReturn([$buttonOne, $buttonTwo]);

        $templating->render('button_one.html.twig', Argument::cetera())->shouldNotBeCalled();

        $templating
            ->render(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('<button id="second_button">Second button</button>');

        $this->renderViewElements([], 'button')->shouldReturn('<button id="second_button">Second button</button>');
    }

    function it_passes_alias_and_loop_context_to_the_view_element_templates($registry, $templating)
    {
        $templating
            ->render(
                'button_one.html.twig',
                ['viewElement' => ['alias' => 'first button', 'loop' => ['index' => 1, 'first' => true, 'last' => false, 'length' => 3]]]
            )
            ->shouldBeCalled()
            ->willReturn('<button id="first_button">First button</button>');

        $templating
            ->render(
                'button_two.html.twig',
                ['viewElement' => ['alias' => 'second button', 'loop' => ['index' => 2, 'first' => false, 'last' => false, 'length' => 3]]]
            )
            ->shouldBeCalled()
            ->willReturn('<button id="second_button">Second button</button>');

        $templating
            ->render(
                'button_three.html.twig',
                ['viewElement' => ['alias' => 'third button', 'loop' => ['index' => 3, 'first' => false, 'last' => true, 'length' => 3]]]
            )
            ->shouldBeCalled()
            ->willReturn('<button id="third_button">Third button</button>');

        $this->renderViewElements([], 'button')->shouldReturn(
            '<button id="first_button">First button</button>' .
            '<button id="second_button">Second button</button>' .
            '<button id="third_button">Third button</button>'
        );
    }

    function it_passes_view_element_parameters_to_their_templates($registry, $templating, $buttonOne, $buttonTwo, $buttonThree)
    {
        $originalContext = [
            'color' => [
                'text' => 'black'
            ]
        ];

        $buttonOne->isVisible(Argument::any())->willReturn(false);

        $buttonTwo->getParameters($originalContext)->willReturn(['color' => 'green', 'textColor' => 'black']);
        $buttonThree->getParameters($originalContext)->willReturn(['color' => ['text' => 'white', 'shadow' => 'grey']]);

        $templating
            ->render(
                'button_two.html.twig',
                [
                    'viewElement' => ['alias' => 'second button', 'loop' => ['index' => 1, 'first' => true, 'last' => false, 'length' => 2]],
                    'color'       => 'green',
                    'textColor'   => 'black'
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<button id="second_button">Second button</button>');

        $templating
            ->render(
                'button_three.html.twig',
                [
                    'viewElement' => ['alias' => 'third button', 'loop' => ['index' => 2, 'first' => false, 'last' => true, 'length' => 2]],
                    'color' => [
                        'text'   => 'white',
                        'shadow' => 'grey'
                    ]
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<button id="third_button">Third button</button>');

        $this->renderViewElements($originalContext, 'button')->shouldReturn(
            '<button id="second_button">Second button</button>' .
            '<button id="third_button">Third button</button>'
        );
    }

    function it_returns_an_empty_string_if_no_visible_elements_of_the_requested_type_are_found($registry, $buttonOne)
    {
        $registry->get('button')->willReturn([$buttonOne]);

        $buttonOne->isVisible(Argument::any())->willReturn(false);

        $this->renderViewElements([], 'button')->shouldReturn('');
    }

    function it_provides_a_list_of_visible_view_element_aliases_for_the_given_type($registry)
    {
        $this->getViewElementAliases([], 'button')->shouldReturn(['first button', 'second button', 'third button']);

        $registry->get('foo')->willReturn([]);
        $this->getViewElementAliases([], 'foo')->shouldReturn([]);
    }

    function getMatchers(): array
    {
        return [
            'haveTwigMethod' => function ($subject, $name, $method, $needsContext, $safe) {
                $function = array_filter(
                    $subject,
                    function ($function) use ($name, $needsContext, $safe) {
                        return $function instanceof \Twig_SimpleFunction &&
                            $function->getName() === $name &&
                            $function->needsContext() === $needsContext &&
                            $function->getSafe(new \Twig_Node()) === $safe;
                    }
                );

                if (count($function) !== 1) {
                    return false;
                }

                $function = array_shift($function);

                return $function->getCallable() === [$this->getWrappedObject(), $method];
            }
        ];
    }
}

<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\NotScopableAttribute;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotScopableAttributeException;
use PhpSpec\ObjectBehavior;

class NotScopableAttributeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(NotScopableAttribute::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_scopable_attribute()
    {
        $scopableException = NotScopableAttributeException::withCode('attribute_code');

        $this->support($scopableException)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error()
    {
        $exception = new \Exception();

        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation()
    {
        $exception = NotScopableAttributeException::withCode('attribute_code');

        $documentation = $this->buildDocumentation($exception);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check the {attribute_edit_route}.',
                'parameters' => [
                    'attribute_edit_route' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_attribute_edit',
                        'routeParameters' => ['code' => 'attribute_code'],
                        'title' => 'attribute_code attributes settings',
                    ],
                ],
                'style' => 'text'
            ],
            [
                'message' => 'More information about channels: {manage_channel}',
                'parameters' => [
                    'manage_channel' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-channels.html',
                        'title' => 'Manage your channels',
                    ],
                ],
                'style' => 'information'
            ]
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_error(\Exception $exception)
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$exception]);
    }
}

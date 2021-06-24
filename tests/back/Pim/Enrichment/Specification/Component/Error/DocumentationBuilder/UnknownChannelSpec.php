<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\UnknownChannel;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ScopableValues;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownChannelSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(UnknownChannel::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_scopable_values_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(ScopableValues::SCOPABLE_VALUES);
        $this->support($constraintViolation)->shouldReturn(true);
    }

    function it_does_not_support_a_random_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $this->support($constraintViolation)->shouldReturn(false);
    }

    function it_does_not_support_a_random_object(\Exception $exception)
    {
        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation(ConstraintViolationInterface $constraintViolation)
    {
        $constraintViolation->getCode()->willReturn(ScopableValues::SCOPABLE_VALUES);

        $documentation = $this->buildDocumentation($constraintViolation);

        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check your {channels_settings}.',
                'parameters' => [
                    'channels_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_channel_index',
                        'routeParameters' => [],
                        'title' => 'Channel settings',
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

    function it_throws_an_exception_on_documentation_build_with_unsupported_constraint(ConstraintViolationInterface $constraintViolation)
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$constraintViolation]);
    }
}

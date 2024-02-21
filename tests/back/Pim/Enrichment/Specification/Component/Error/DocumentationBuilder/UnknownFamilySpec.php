<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\UnknownFamily;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownFamilyException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UnknownFamilySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(UnknownFamily::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_unknown_family()
    {
        $exception = new UnknownFamilyException('family', 'family_code', self::class);

        $this->support($exception)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error()
    {
        $exception = new \Exception();

        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation()
    {
        $exception = new UnknownFamilyException('family', 'family_code', self::class);

        $documentation = $this->buildDocumentation($exception);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check your {family_settings}.',
                'parameters' => [
                    'family_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_family_index',
                        'routeParameters' => [],
                        'title' => 'Family settings',
                    ],
                ],
                'style' => 'text'
            ],
            [
                'message' => 'More information about families: {what_is_a_family} {manage_your_families}.',
                'parameters' => [
                    'what_is_a_family' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-a-family.html',
                        'title' => 'What is a family?',
                    ],
                    'manage_your_families' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-families.html',
                        'title' => 'Manage your families',
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

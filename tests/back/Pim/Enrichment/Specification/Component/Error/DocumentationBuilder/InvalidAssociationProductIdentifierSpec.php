<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\InvalidAssociationProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAssociationProductIdentifierException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidAssociationProductIdentifierSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(InvalidAssociationProductIdentifier::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_invalid_association_product_identifier()
    {
        $exception = new InvalidAssociationProductIdentifierException(self::class, 'product_identifier');

        $this->support($exception)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error()
    {
        $exception = new \Exception();

        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation()
    {
        $exception = new InvalidAssociationProductIdentifierException(self::class, 'product_identifier');

        $documentation = $this->buildDocumentation($exception);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check if the product exists in your PIM or check {permissions_settings}.',
                'parameters' => [
                    'permissions_settings' => [
                        'type' => 'route',
                        'route' => 'pim_user_group_index',
                        'routeParameters' => [],
                        'title' => 'your connection group permissions settings',
                    ],
                ],
                'style' => 'text'
            ],
            [
                'message' => 'More information about connection permissions: {manage_your_connections}.',
                'parameters' => [
                    'manage_your_connections' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#configure-the-connection-user-group',
                        'title' => 'Manage your connections',
                    ],
                ],
                'style' => 'information'
            ],
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_error(\Exception $exception)
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$exception]);
    }
}

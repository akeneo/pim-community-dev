<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderRegistry;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DocumentationBuilderRegistrySpec extends ObjectBehavior
{
    function let(DocumentationBuilderInterface $builder)
    {
        $this->beConstructedWith([$builder]);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(DocumentationBuilderRegistry::class);
    }

    function it_returns_the_object_documentation_from_a_builder($builder)
    {
        $object = new class ()
        {
        };
        $documentation = new DocumentationCollection([]);

        $builder->support($object)->willReturn(true);
        $builder->buildDocumentation($object)->willReturn($documentation);

        $this->getDocumentation($object)->shouldReturn($documentation);
    }

    function it_returns_null_if_no_builder_support_the_object($builder)
    {
        $object = new class ()
        {
        };
        $builder->support($object)->willReturn(false);

        $this->getDocumentation($object)->shouldReturn(null);
    }
}

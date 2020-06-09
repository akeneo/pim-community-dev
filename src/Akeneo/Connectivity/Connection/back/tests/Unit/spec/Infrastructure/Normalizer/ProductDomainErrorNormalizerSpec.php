<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Connectivity\Connection\Infrastructure\Normalizer\ProductDomainErrorNormalizer;
use Akeneo\Pim\Enrichment\Component\Error\Documented\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentedErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductDomainErrorNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ProductDomainErrorNormalizer::class);
    }

    public function it_is_cacheable(): void
    {
        $this->shouldImplement(CacheableSupportsMethodInterface::class);
        $this->hasCacheableSupportsMethod()->shouldReturn(true);
    }

    public function it_supports_an_identifiable_domain_error(DomainErrorInterface $error): void
    {
        $this->supportsNormalization($error)->shouldReturn(true);
    }

    public function it_normalizes_an_identifiable_domain_error(): void
    {
        $error = new class () implements DomainErrorInterface
        {
        };

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error'
        ]);
    }

    public function it_normalizes_an_exception(): void
    {
        $error = new class ('My message.') extends \Exception implements DomainErrorInterface
        {
        };

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error',
            'message' => 'My message.'
        ]);
    }

    public function it_normalizes_a_templated_error_message(): void
    {
        $error = new class () implements DomainErrorInterface, TemplatedErrorMessageInterface
        {
            public function getMessageTemplate(): string
            {
                return 'My message template with %param%.';
            }

            public function getMessageParameters(): array
            {
                return ['%param%' => 'a param'];
            }
        };

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error',
            'message_template' => 'My message template with %param%.',
            'message_parameters' => ['%param%' => 'a param']
        ]);
    }

    public function it_normalizes_a_documented_error(): void
    {
        $error = new class () implements DomainErrorInterface, DocumentedErrorInterface
        {
            public function getDocumentation(): DocumentationCollection
            {
                return new DocumentationCollection([new Documentation('any message', [])]);
            }
        };

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error',
            'documentation' => [
                [
                    'message' => 'any message',
                    'parameters' => [],
                ],
            ]
        ]);
    }

    public function it_normalizes_the_product_information(ProductInterface $product): void
    {
        $error = new class () implements DomainErrorInterface
        {
        };

        $product->getId()->willReturn(1);
        $product->getIdentifier()->willReturn('product_identifier');

        $this->normalize($error, 'json', ['product' => $product])->shouldReturn([
            'type' => 'domain_error',
            'product' => [
                'id' => 1,
                'identifier' => 'product_identifier'
            ]
        ]);
    }
}

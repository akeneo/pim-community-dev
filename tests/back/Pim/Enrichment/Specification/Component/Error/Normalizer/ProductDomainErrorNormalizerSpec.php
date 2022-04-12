<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderRegistry;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\Normalizer\ProductDomainErrorNormalizer;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductDomainErrorNormalizerSpec extends ObjectBehavior
{
    public function let(DocumentationBuilderRegistry $documentationBuilderRegistry): void
    {
        $documentationBuilderRegistry->getDocumentation(Argument::any())->willReturn(null);

        $this->beConstructedWith($documentationBuilderRegistry);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ProductDomainErrorNormalizer::class);
    }

    public function it_is_cacheable(): void
    {
        $this->shouldImplement(CacheableSupportsMethodInterface::class);
        $this->hasCacheableSupportsMethod()->shouldReturn(true);
    }

    public function it_supports_a_domain_error(DomainErrorInterface $error): void
    {
        $this->supportsNormalization($error)->shouldReturn(true);
    }

    public function it_normalizes_a_domain_error(): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
        {
        };

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error',
            'message' => 'Some error message',
        ]);
    }

    public function it_normalizes_a_templated_error_message(): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface, TemplatedErrorMessageInterface
        {
            public function getTemplatedErrorMessage(): TemplatedErrorMessage
            {
                return new TemplatedErrorMessage('My message template with {param}.', ['param' => 'a param']);
            }
        };

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error',
            'message' => 'Some error message',
            'message_template' => 'My message template with {param}.',
            'message_parameters' => ['param' => 'a param']
        ]);
    }

    public function it_normalizes_the_product_without_family(ProductInterface $product): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
        {
        };

        $product->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->getIdentifier()->willReturn('product_identifier');
        $product->getFamily()->willReturn(null);
        $product->getLabel()->willReturn('Akeneo T-Shirt black and purple with short sleeve');

        $this->normalize($error, 'json', ['product' => $product])->shouldReturn([
            'type' => 'domain_error',
            'message' => 'Some error message',
            'product' => [
                'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                'identifier' => 'product_identifier',
                'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                'family' => null,
            ]
        ]);
    }

    public function it_normalizes_a_documented_error($documentationBuilderRegistry): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
        {
        };

        $documentationBuilderRegistry->getDocumentation($error)->willReturn(new DocumentationCollection([]));

        $this->normalize($error, 'json', [])->shouldReturn([
            'type' => 'domain_error',
            'message' => 'Some error message',
            'documentation' => []
        ]);
    }

    public function it_normalizes_the_product_information(ProductInterface $product, FamilyInterface $family): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
        {
        };

        $product->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->getIdentifier()->willReturn('product_identifier');
        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $product->getLabel()->willReturn('Akeneo T-Shirt black and purple with short sleeve');

        $this->normalize($error, 'json', ['product' => $product])->shouldReturn([
            'type' => 'domain_error',
            'message' => 'Some error message',
            'product' => [
                'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                'identifier' => 'product_identifier',
                'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                'family' => 'tshirts',
            ]
        ]);
    }
}

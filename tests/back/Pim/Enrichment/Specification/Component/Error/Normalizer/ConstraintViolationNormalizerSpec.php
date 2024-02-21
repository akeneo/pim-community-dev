<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderRegistry;
use Akeneo\Pim\Enrichment\Component\Error\Normalizer\ConstraintViolationNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationNormalizerSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        DocumentationBuilderRegistry $documentationBuilderRegistry
    ): void {
        $documentationBuilderRegistry->getDocumentation(Argument::any())->willReturn(null);

        $this->beConstructedWith($attributeRepository, $documentationBuilderRegistry);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ConstraintViolationNormalizer::class);
    }

    public function it_is_cacheable(): void
    {
        $this->hasCacheableSupportsMethod()->shouldReturn(true);
    }

    public function it_supports_constraint_violation(ConstraintViolationInterface $constraintViolation): void
    {
        $this->supportsNormalization($constraintViolation)->shouldReturn(true);
    }

    public function it_normalizes_a_constraint_violation($documentationBuilderRegistry): void
    {
        $constraintViolation = new ConstraintViolation(
            'Property "clothing_size" expects a valid code. The option "z" does not exist',
            'Property "%attribute_code%" expects a valid code. The option "%invalid_option%" does not exist',
            [
                '%attribute_code%' => 'clothing_size',
                '%invalid_option%' => 'z'
            ],
            '',
            'values',
            ''
        );

        $documentationBuilderRegistry->getDocumentation($constraintViolation)
            ->willReturn(new DocumentationCollection([]));

        $this->normalize($constraintViolation)->shouldReturn(
            [
                'property' => 'values',
                'message' => 'Property "clothing_size" expects a valid code. The option "z" does not exist',
                'type' => 'violation_error',
                'message_template' => 'Property "%attribute_code%" expects a valid code. The option "%invalid_option%" does not exist',
                'message_parameters' => [
                    '%attribute_code%' => 'clothing_size',
                    '%invalid_option%' => 'z'
                ],
                'documentation' => []
            ]
        );
    }

    public function it_normalizes_a_product_without_family(ProductInterface $product): void
    {
        $constraintViolation = new ConstraintViolation('', '', [], '', '', '');

        $product->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->getIdentifier()->willReturn('product_identifier');
        $product->getFamily()->willReturn(null);
        $product->getLabel()->willReturn('Akeneo T-Shirt black and purple with short sleeve');

        $this->normalize($constraintViolation, 'json', ['product' => $product])->shouldReturn([
            'property' => '',
            'message' => '',
            'type' => 'violation_error',
            'message_template' => '',
            'message_parameters' => [],
            'product' => [
                'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                'identifier' => 'product_identifier',
                'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                'family' => null,
            ]
        ]);
    }

    public function it_normalizes_a_product(ProductInterface $product, FamilyInterface $family): void
    {
        $constraintViolation = new ConstraintViolation('', '', [], '', '', '');

        $product->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->getIdentifier()->willReturn('product_identifier');
        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $product->getLabel()->willReturn('Akeneo T-Shirt black and purple with short sleeve');

        $this->normalize($constraintViolation, 'json', ['product' => $product])->shouldReturn([
            'property' => '',
            'message' => '',
            'type' => 'violation_error',
            'message_template' => '',
            'message_parameters' => [],
            'product' => [
                'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                'identifier' => 'product_identifier',
                'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                'family' => 'tshirts',
            ]
        ]);
    }
}

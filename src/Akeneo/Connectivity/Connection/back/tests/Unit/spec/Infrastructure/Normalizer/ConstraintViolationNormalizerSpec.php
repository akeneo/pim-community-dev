<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Connectivity\Connection\Infrastructure\Normalizer\ConstraintViolationNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationNormalizerSpec extends ObjectBehavior
{
    public function let(IdentifiableObjectRepositoryInterface $attributeRepository): void
    {
        $this->beConstructedWith($attributeRepository);
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

    public function it_normalizes_a_constraint_violation(): void
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

        $this->normalize($constraintViolation)->shouldReturn(
            [
                'property' => 'values',
                'message' => 'Property "clothing_size" expects a valid code. The option "z" does not exist',
                'type' => 'violation_error',
                'message_template' => 'Property "%attribute_code%" expects a valid code. The option "%invalid_option%" does not exist',
                'message_parameters' => [
                    '%attribute_code%' => 'clothing_size',
                    '%invalid_option%' => 'z'
                ]
            ]
        );
    }

    public function it_normalizes_the_product_information(ProductInterface $product): void
    {
        $constraintViolation = new ConstraintViolation('', '', [], '', '', '');

        $product->getId()->willReturn(1);
        $product->getIdentifier()->willReturn('product_identifier');

        $this->normalize($constraintViolation, 'json', ['product' => $product])->shouldReturn([
            'property' => '',
            'message' => '',
            'type' => 'violation_error',
            'message_template' => '',
            'message_parameters' => [],
            'product' => [
                'id' => 1,
                'identifier' => 'product_identifier'
            ]
        ]);
    }
}

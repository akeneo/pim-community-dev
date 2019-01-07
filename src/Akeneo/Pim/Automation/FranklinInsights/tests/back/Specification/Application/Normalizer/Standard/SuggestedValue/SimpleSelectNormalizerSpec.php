<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue\SimpleSelectNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SimpleSelectNormalizerSpec extends ObjectBehavior
{
    public function let(AttributeOptionRepositoryInterface $attributeOptionRepository): void
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    public function it_is_a_suggested_value_simple_select_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(SimpleSelectNormalizer::class);
    }

    public function it_normalizes_a_simple_select_suggested_value(
        $attributeOptionRepository,
        AttributeOptionInterface $attributeOption
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', 'an_option');

        $attributeOptionRepository
            ->findOneByIdentifier('attribute_code.an_option')
            ->willReturn($attributeOption);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 'an_option',
            ]],
        ]);
    }

    public function it_returns_an_emtpy_array_if_the_option_does_not_exist_in_the_pim($attributeOptionRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'an_option_that_does_not_exist');

        $attributeOptionRepository
            ->findOneByIdentifier('attribute_code.an_option_that_does_not_exist')
            ->willReturn(null);

        $this->normalize($suggestedValue)->shouldReturn([]);
    }
}

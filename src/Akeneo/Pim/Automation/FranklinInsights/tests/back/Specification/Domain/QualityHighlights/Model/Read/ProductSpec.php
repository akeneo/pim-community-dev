<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

class ProductSpec extends ObjectBehavior
{
    function it_gives_its_id()
    {
        $productId = new ProductId(42);

        $this->beConstructedWith(
            $productId,
            new FamilyCode('mugs'),
            []
        );

        $this->getId()->shouldReturn($productId);
    }

    function it_gives_its_family_code()
    {
        $familyCode = new FamilyCode('mugs');

        $this->beConstructedWith(
            new ProductId(42),
            $familyCode,
            []
        );

        $this->getFamilyCode()->shouldReturn($familyCode);
    }

    function it_gives_its_raw_values()
    {
        $rawValues = [
            'name' => [
                'ecommerce' => [
                    'en_US' => 'Ziggy'
                ]
            ]
        ];

        $this->beConstructedWith(
            new ProductId(42),
            new FamilyCode('mugs'),
            $rawValues
        );

        $this->getRawValues()->shouldReturn($rawValues);
    }
}

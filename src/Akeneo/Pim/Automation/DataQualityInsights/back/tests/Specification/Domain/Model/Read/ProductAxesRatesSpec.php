<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class ProductAxesRatesSpec extends ObjectBehavior
{
    public function it_returns_product_axes_rates_ranks()
    {
        $this->beConstructedWith(new ProductId(42), [
            'consistency' => [
                'ecommerce' => [
                    'en_US' => ['value' => 98, 'rank' => 1],
                    'fr_FR' => ['value' => 84, 'rank' => 2],
                ],
                'mobile' => [
                    'en_US' => ['value' => 88, 'rank' => 2],
                    'fr_FR' => ['value' => 74, 'rank' => 3],
                ]
            ],
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => ['value' => 12, 'rank' => 5]
                ]
            ],
        ]);

        $this->getRanks()->shouldReturn([
            'consistency' => [
                'ecommerce' => [
                    'en_US' => 1,
                    'fr_FR' => 2,
                ],
                'mobile' => [
                    'en_US' => 2,
                    'fr_FR' => 3,
                ]
            ],
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => 5
                ]
            ],
        ]);
    }

    public function it_returns_product_axes_rates_values()
    {
        $this->beConstructedWith(new ProductId(42), [
            'consistency' => [
                'ecommerce' => [
                    'en_US' => ['value' => 98, 'rank' => 1],
                    'fr_FR' => ['value' => 84, 'rank' => 2],
                ],
                'mobile' => [
                    'en_US' => ['value' => 88, 'rank' => 2],
                    'fr_FR' => ['value' => 74, 'rank' => 3],
                ]
            ],
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => ['value' => 12, 'rank' => 5]
                ]
            ],
        ]);

        $this->getValues()->shouldReturn([
            'consistency' => [
                'ecommerce' => [
                    'en_US' => 98,
                    'fr_FR' => 84,
                ],
                'mobile' => [
                    'en_US' => 88,
                    'fr_FR' => 74,
                ]
            ],
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => 12
                ]
            ],
        ]);
    }

    public function it_throws_an_exception_if_the_product_rates_are_malformed()
    {
        $this->beConstructedWith(new ProductId(42), [
            'consistency' => [
                'ecommerce' => [
                    'en_US' => ['wtf' => 98, 'rank' => 1],
                    'fr_FR' => ['value' => 84, 'nope' => 2],
                ],
            ],
            'enrichment' => [
                'ecommerce' => 42
            ],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}

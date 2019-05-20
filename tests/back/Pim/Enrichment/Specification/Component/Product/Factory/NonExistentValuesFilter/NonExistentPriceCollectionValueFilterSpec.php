<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentPriceCollectionValueFilter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistentPriceCollectionValueFilterSpec extends ObjectBehavior
{
    public function let(FindActivatedCurrenciesInterface $findActivatedCurrencies) {
        $this->beConstructedWith($findActivatedCurrencies);
    }

    public function it_has_a_type()
    {
        $this->shouldHaveType(NonExistentPriceCollectionValueFilter::class);
    }

    public function it_filters_price_collection_values(FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::PRICE_COLLECTION => [
                    'a_price_collection' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'ecommerce' => [
                                    'en_US' => [
                                        ['currency' => 'USD', 'amount' => '12.05']
                                    ],
                                ],
                                'tablet' => [
                                    'fr_FR' => [
                                        ['currency' => 'EUR', 'amount' => '14'],
                                        ['currency' => 'EUR', 'amount' => '16.04']
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],
                AttributeTypes::TEXTAREA => [
                    'a_description' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'plop'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $findActivatedCurrencies->forChannel('ecommerce')->willReturn(['EUR']);
        $findActivatedCurrencies->forChannel('tablet')->willReturn(['EUR']);

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::PRICE_COLLECTION => [
                    'a_price_collection' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                'tablet' => [
                                    'fr_FR' => [
                                        ['currency' => 'EUR', 'amount' => '16.04']
                                    ],
                                ],
                            ],
                            'properties' => [],
                        ]
                    ]
                ],
            ]
        );
    }
}

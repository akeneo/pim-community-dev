<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for price collection attribute type.
 * The minimal catalog has originally one channel "ecommerce", with one currency "USD".
 * For these tests, we added:
 *      - the channel "print" with the currency "EUR"
 *      - the channel "tablet" with the currencies "USD" and "EUR"
 *
 * A "price" price collection attribute is required on the 3 channels.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeTypeCompletenessIntegration extends AbstractCompletenessTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createChannelWithCurrencies('print', 'master', ['EUR']);
        $this->createChannelWithCurrencies('tablet', 'master', ['USD', 'EUR']);

        $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_price_collection',
            AttributeTypes::PRICE_COLLECTION
        );

        $this->addFamilyRequirement('another_family', 'print', 'a_price_collection');
        $this->addFamilyRequirement('another_family', 'tablet', 'a_price_collection');
    }

    public function testCompletePriceCollectionAllChannels()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('another_family');

        $productCompleteAllChannels = $this->createProductWithStandardValues(
            $family,
            'product_complete_all_channels',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 42,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => 69,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        );
        $this->assertCompleteOnChannel($productCompleteAllChannels, 'ecommerce');
        $this->assertCompleteOnChannel($productCompleteAllChannels, 'print');
        $this->assertCompleteOnChannel($productCompleteAllChannels, 'tablet');

        $productAllChannelsAmountsZero = $this->createProductWithStandardValues(
            $family,
            'product_complete_all_channels_amounts_zero',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 0,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => 0,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        );
        $this->assertCompleteOnChannel($productAllChannelsAmountsZero, 'ecommerce');
        $this->assertCompleteOnChannel($productAllChannelsAmountsZero, 'print');
        $this->assertCompleteOnChannel($productAllChannelsAmountsZero, 'tablet');
    }

    public function testCompletePriceCollectionTakingCurrenciesOfTheChannelIntoAccount()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('another_family');

        $productCompleteEcommerce = $this->createProductWithStandardValues(
            $family,
            'product_complete_ecommerce',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 42,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        );
        $this->assertCompleteOnChannel($productCompleteEcommerce, 'ecommerce');
        $this->assertNotCompleteOnChannel($productCompleteEcommerce, 'print', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productCompleteEcommerce, 'tablet', ['a_price_collection']);

        $productCompletePrint = $this->createProductWithStandardValues(
            $family,
            'product_complete_print',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 42,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        );
        $this->assertNotCompleteOnChannel($productCompletePrint, 'ecommerce', ['a_price_collection']);
        $this->assertCompleteOnChannel($productCompletePrint, 'print');
        $this->assertNotCompleteOnChannel($productCompletePrint, 'tablet', ['a_price_collection']);
    }

    public function testNotCompletePriceCollection()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('another_family');

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotCompleteOnChannel($productWithoutValues, 'ecommerce', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productWithoutValues, 'print', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productWithoutValues, 'tablet', ['a_price_collection']);

        $productAmountsNull = $this->createProductWithStandardValues(
            $family,
            'product_amounts_null',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => null,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => null,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotCompleteOnChannel($productAmountsNull, 'ecommerce', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productAmountsNull, 'print', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productAmountsNull, 'tablet', ['a_price_collection']);

        $productAmountNull = $this->createProductWithStandardValues(
            $family,
            'product_amount_null',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 7,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => null,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertCompleteOnChannel($productAmountNull, 'ecommerce');
        $this->assertNotCompleteOnChannel($productAmountNull, 'print', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productAmountNull, 'tablet', ['a_price_collection']);

        $productMissingPrice = $this->createProductWithStandardValues(
            $family,
            'product_missing_price',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 67,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertCompleteOnChannel($productMissingPrice, 'ecommerce');
        $this->assertNotCompleteOnChannel($productMissingPrice, 'print', ['a_price_collection']);
        $this->assertNotCompleteOnChannel($productMissingPrice, 'tablet', ['a_price_collection']);
    }

    /**
     * @param string $channelCode
     * @param string $categoryCode
     * @param array  $currencyCodes
     *
     * @return ChannelInterface
     * @internal param array $localeCodes
     */
    private function createChannelWithCurrencies($channelCode, $categoryCode, array $currencyCodes)
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($categoryCode);

        $channel = $this->get('pim_catalog.factory.channel')->create();
        $channel->setCode($channelCode);
        $channel->setCategory($category);

        foreach ($currencyCodes as $currencyCode) {
            $currency = $this->findOrCreateCurrency($currencyCode);
            $channel->addCurrency($currency);
        }

        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US');
        $channel->addLocale($locale);

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    /**
     * @param string $code
     *
     * @return CurrencyInterface
     */
    private function findOrCreateCurrency($code)
    {
        $currency = $this->get('pim_catalog.repository.currency')->findOneByIdentifier($code);
        if (null === $currency) {
            $currency = $this->get('pim_catalog.factory.currency')->create();
            $currency->setCode($code);
            $currency->setActivated(true);
            $this->get('pim_catalog.saver.currency')->save($currency);

        }

        return $currency;
    }

    /**
     * Here, the identifier and the attribute should be filled in.
     * Which means, there should be 0 missing, and 2 required.
     *
     * @param ProductInterface $product
     * @param string           $channelCode
     */
    private function assertCompleteOnChannel(ProductInterface $product, $channelCode)
    {
        $this->assertCompletenessesCount($product, 3);

        $completeness = $this->getCompletenessByChannel($product, $channelCode);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals($channelCode, $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals(2, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
        $this->assertEquals(0, $completeness->getMissingAttributes()->count());
    }

    /**
     * Here, only the identifier should be filled in.
     * Which means, there should be 1 missing, and 2 required.
     *
     * @param ProductInterface $product
     * @param string           $channelCode
     * @param string[]         $expectedAttributeCodes
     */
    private function assertNotCompleteOnChannel(ProductInterface $product, $channelCode, array $expectedAttributeCodes)
    {
        $this->assertCompletenessesCount($product, 3);

        $completeness = $this->getCompletenessByChannel($product, $channelCode);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals($channelCode, $completeness->getChannel()->getCode());
        $this->assertEquals(50, $completeness->getRatio());
        $this->assertEquals(2, $completeness->getRequiredCount());
        $this->assertEquals(1, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, $expectedAttributeCodes);
    }

    /**
     * @param ProductInterface $product
     * @param string           $channelCode
     *
     * @throws \Exception
     * @return CompletenessInterface
     */
    private function getCompletenessByChannel(ProductInterface $product, $channelCode)
    {
        $completenesses = $product->getCompletenesses()->toArray();

        foreach ($completenesses as $completeness) {
            if ($channelCode === $completeness->getChannel()->getCode()) {
                return $completeness;
            }
        }

        throw new \Exception(
            sprintf(
                'No completeness found for the product "%s" and the channel "%s"',
                $product->getIdentifier(),
                $channelCode
            )
        );
    }
}

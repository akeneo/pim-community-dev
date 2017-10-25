<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for localisable and scopable attributes.
 *
 * We test from the footwear catalog that contains 2 channels, with 2 activated locales for each channel.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForScopableAndLocalisableAttributeIntegration extends AbstractCompletenessTestCase
{
    public function testProductIncomplete()
    {
        $sandalsFamily = $this->get('pim_catalog.repository.family')->findOneByIdentifier('sandals');

        $sandals = $this->createProductWithStandardValues(
            $sandalsFamily,
            'sandals',
            ['values' => $this->getSandalStandardValues()]
        );

        $completenesses = $sandals->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(4, $completenesses);

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'mobile', 'en_US');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('mobile', $completeness->getChannel()->getCode());
        $this->assertEquals(40, $completeness->getRatio());
        $this->assertEquals(5, $completeness->getRequiredCount());
        $this->assertEquals(3, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, ['name', 'price', 'size']);

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'tablet', 'en_US');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('tablet', $completeness->getChannel()->getCode());
        $this->assertEquals(25, $completeness->getRatio());
        $this->assertEquals(8, $completeness->getRequiredCount());
        $this->assertEquals(6, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes(
            $completeness,
            ['name', 'price', 'size', 'description', 'rating', 'side_view']
        );

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'mobile', 'fr_FR');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('fr_FR', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('mobile', $completeness->getChannel()->getCode());
        $this->assertEquals(60, $completeness->getRatio());
        $this->assertEquals(5, $completeness->getRequiredCount());
        $this->assertEquals(2, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, ['price', 'size']);

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'tablet', 'fr_FR');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('fr_FR', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('tablet', $completeness->getChannel()->getCode());
        $this->assertEquals(50, $completeness->getRatio());
        $this->assertEquals(8, $completeness->getRequiredCount());
        $this->assertEquals(4, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, ['price', 'size', 'rating', 'side_view']);
    }

    public function testProductCompleteOnOneChannel()
    {
        $sneakersFamily = $this->get('pim_catalog.repository.family')->findOneByIdentifier('sneakers');

        $sandals = $this->createProductWithStandardValues(
            $sneakersFamily,
            'sneakers',
            ['values' => $this->getSneakerStandardValues()]
        );

        $completenesses = $sandals->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(4, $completenesses);

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'mobile', 'en_US');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('mobile', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals(5, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
        $this->assertEquals(0, $completeness->getMissingAttributes()->count());

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'tablet', 'en_US');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('tablet', $completeness->getChannel()->getCode());
        $this->assertEquals(88, $completeness->getRatio());
        $this->assertEquals(9, $completeness->getRequiredCount());
        $this->assertEquals(1, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, ['side_view']);

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'mobile', 'fr_FR');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('fr_FR', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('mobile', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals(5, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
        $this->assertEquals(0, $completeness->getMissingAttributes()->count());

        $completeness = $this->getCompletenessByChannelAndLocaleCodes($sandals, 'tablet', 'fr_FR');
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('fr_FR', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('tablet', $completeness->getChannel()->getCode());
        $this->assertEquals(77, $completeness->getRatio());
        $this->assertEquals(9, $completeness->getRequiredCount());
        $this->assertEquals(2, $completeness->getMissingCount());
        $this->assertMissingAttributeCodes($completeness, ['description', 'side_view']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $channels = $this->get('pim_catalog.repository.channel')->findAll();
        foreach ($channels as $channel) {
            $channel->addLocale($fr);
        }
        $this->get('pim_catalog.saver.channel')->saveAll($channels);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }

    /**
     * @param ProductInterface $product
     * @param string           $channelCode
     * @param string           $localeCode
     *
     * @throws \Exception
     * @return CompletenessInterface
     */
    private function getCompletenessByChannelAndLocaleCodes(ProductInterface $product, $channelCode, $localeCode)
    {
        $completenesses = $product->getCompletenesses()->toArray();

        foreach ($completenesses as $completeness) {
            if ($localeCode === $completeness->getLocale()->getCode() &&
                $channelCode === $completeness->getChannel()->getCode()) {
                return $completeness;
            }
        }

        throw new \Exception(sprintf(
            'No completeness for the channel "%s" and locale "%s"',
            $channelCode,
            $localeCode
        ));
    }

    /**
     * @return array
     */
    private function getSandalStandardValues()
    {
        return [
            'color' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'white'
                ],
            ],
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => 'Sandales'
                ],
            ],
            'description' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'mobile',
                    'data'   => 'Super sandales !'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'tablet',
                    'data'   => 'Des sandales magiques !'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getSneakerStandardValues()
    {
        return [
            'manufacturer' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'converse'
                ],
            ],
            'weather_conditions' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['hot']
                ],
            ],
            'color' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'blue'
                ],
            ],
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => 'Sneakers'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => 'Espadrilles'
                ],
            ],
            'price' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        ['amount' => 69, 'currency' => 'EUR'],
                        ['amount' => 99, 'currency' => 'USD'],
                    ]

                ],
            ],
            'rating' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 4
                ],
            ],
            'size' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 43
                ],
            ],
            'lace_color' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'laces_white'
                ],
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'data'   => 'Great sneakers'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'tablet',
                    'data'   => 'Really great sneakers'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'mobile',
                    'data'   => 'Grandes espadrilles'
                ],
            ],
        ];
    }
}

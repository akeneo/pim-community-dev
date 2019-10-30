<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

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

        $completenesses = $this->getProductCompletenesses()->fromProductId($sandals->getId());
        $this->assertCount(4, $completenesses);

        $completeness = $completenesses->getCompletenessForChannelAndLocale('mobile', 'en_US');
        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('mobile', $completeness->channelCode());
        $this->assertEquals(40, $completeness->ratio());
        $this->assertEquals(5, $completeness->requiredCount());
        $this->assertEquals(3, $completeness->missingCount());

        $completeness = $completenesses->getCompletenessForChannelAndLocale('tablet', 'en_US');
        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('tablet', $completeness->channelCode());
        $this->assertEquals(25, $completeness->ratio());
        $this->assertEquals(8, $completeness->requiredCount());
        $this->assertEquals(6, $completeness->missingCount());

        $completeness = $completenesses->getCompletenessForChannelAndLocale('mobile', 'fr_FR');
        $this->assertEquals('fr_FR', $completeness->localeCode());
        $this->assertEquals('mobile', $completeness->channelCode());
        $this->assertEquals(60, $completeness->ratio());
        $this->assertEquals(5, $completeness->requiredCount());
        $this->assertEquals(2, $completeness->missingCount());

        $completeness = $completenesses->getCompletenessForChannelAndLocale('tablet', 'fr_FR');
        $this->assertEquals('fr_FR', $completeness->localeCode());
        $this->assertEquals('tablet', $completeness->channelCode());
        $this->assertEquals(50, $completeness->ratio());
        $this->assertEquals(8, $completeness->requiredCount());
        $this->assertEquals(4, $completeness->missingCount());
    }

    public function testProductCompleteOnOneChannel()
    {
        $sneakersFamily = $this->get('pim_catalog.repository.family')->findOneByIdentifier('sneakers');

        $sandals = $this->createProductWithStandardValues(
            $sneakersFamily,
            'sneakers',
            ['values' => $this->getSneakerStandardValues()]
        );

        $completenesses = $this->getProductCompletenesses()->fromProductId($sandals->getId());
        $this->assertCount(4, $completenesses);

        $completeness = $completenesses->getCompletenessForChannelAndLocale('mobile', 'en_US');
        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('mobile', $completeness->channelCode());
        $this->assertEquals(100, $completeness->ratio());
        $this->assertEquals(5, $completeness->requiredCount());
        $this->assertEquals(0, $completeness->missingCount());

        $completeness = $completenesses->getCompletenessForChannelAndLocale('tablet', 'en_US');
        $this->assertEquals('en_US', $completeness->localeCode());
        $this->assertEquals('tablet', $completeness->channelCode());
        $this->assertEquals(88, $completeness->ratio());
        $this->assertEquals(9, $completeness->requiredCount());
        $this->assertEquals(1, $completeness->missingCount());

        $completeness = $completenesses->getCompletenessForChannelAndLocale('mobile', 'fr_FR');
        $this->assertEquals('fr_FR', $completeness->localeCode());
        $this->assertEquals('mobile', $completeness->channelCode());
        $this->assertEquals(100, $completeness->ratio());
        $this->assertEquals(5, $completeness->requiredCount());
        $this->assertEquals(0, $completeness->missingCount());

        $completeness = $completenesses->getCompletenessForChannelAndLocale('tablet', 'fr_FR');
        $this->assertEquals('fr_FR', $completeness->localeCode());
        $this->assertEquals('tablet', $completeness->channelCode());
        $this->assertEquals(77, $completeness->ratio());
        $this->assertEquals(9, $completeness->requiredCount());
        $this->assertEquals(2, $completeness->missingCount());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
                    'data'   => 'Converse'
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
                    'data'   => "4"
                ],
            ],
            'size' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => "43"
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

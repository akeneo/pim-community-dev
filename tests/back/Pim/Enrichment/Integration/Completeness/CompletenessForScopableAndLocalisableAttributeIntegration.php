<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;

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
        $this->get('pim_catalog.repository.family')->findOneByIdentifier('sandals');

        $sandals = $this->createProductWithStandardValues(
            'sandals',
            \array_merge(
                [new SetFamily('sandals')],
                $this->getSandalStandardValues()
            )
        );

        $completenesses = $this->getProductCompletenesses()->fromProductUuid($sandals->getUuid());
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
        $this->get('pim_catalog.repository.family')->findOneByIdentifier('sneakers');

        $sandals = $this->createProductWithStandardValues(
            'sneakers',
            \array_merge(
                [new SetFamily('sneakers')],
                $this->getSneakerStandardValues()
            )
        );

        $completenesses = $this->getProductCompletenesses()->fromProductUuid($sandals->getUuid());
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
            new SetSimpleSelectValue('color', null, null, 'white'),
            new SetTextValue('name', null, 'fr_FR', 'Sandales'),
            new SetTextareaValue('description', 'mobile', 'fr_FR', 'Super sandales !'),
            new SetTextareaValue('description', 'tablet', 'fr_FR', 'Des sandales magiques !'),
        ];
    }

    /**
     * @return UserIntent[]
     */
    private function getSneakerStandardValues(): array
    {
        return [
            new SetSimpleSelectValue('manufacturer', null, null, 'Converse'),
            new SetMultiSelectValue('weather_conditions', null, null, ['hot']),
            new SetSimpleSelectValue('color', null, null, 'blue'),
            new SetTextValue('name', null, 'en_US', 'Sneakers'),
            new SetTextValue('name', null, 'fr_FR', 'Espadrilles'),
            new SetPriceCollectionValue('price', null, null, [
                new PriceValue('69', 'EUR'),
                new PriceValue('99', 'USD'),
            ]),
            new SetSimpleSelectValue('rating', null, null, '4'),
            new SetSimpleSelectValue('size', null, null, '43'),
            new SetSimpleSelectValue('lace_color', null, null, 'laces_white'),
            new SetTextareaValue('description', 'mobile', 'en_US', 'Great sneakers'),
            new SetTextareaValue('description', 'tablet', 'en_US', 'Really great sneakers'),
            new SetTextareaValue('description', 'mobile', 'fr_FR', 'Grandes espadrilles'),
        ];
    }
}

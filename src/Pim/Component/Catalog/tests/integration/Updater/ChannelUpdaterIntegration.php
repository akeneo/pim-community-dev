<?php

namespace Pim\Component\Catalog\tests\integration\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ChannelInterface;

class ChannelUpdaterIntegration extends TestCase
{
    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidObjectException
     * @expectedExceptionMessage Expects a "Pim\Component\Catalog\Model\ChannelInterface", "stdClass" given.
     */
    public function testUpdateObjectInChannelUpdater()
    {
        $this->getUpdater()->update(new \stdClass(), []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "labels" expects an array as data, "NULL" given.
     */
    public function testChannelUpdateWithNullLabels()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['labels' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "locales" expects an array as data, "NULL" given.
     */
    public function testChannelUpdateWithNullLocales()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['locales' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "currencies" expects an array as data, "NULL" given.
     */
    public function testChannelUpdateWithNullCurrencies()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['currencies' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "conversion_units" expects an array as data, "NULL" given.
     */
    public function testChannelUpdateWithNullConversionUnits()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['conversion_units' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "labels" values is not a scalar
     */
    public function testChannelUpdateWithNonScalarLabels()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['labels' => ['en_US' => []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "locales" values is not a scalar
     */
    public function testChannelUpdateWithNonScalarLocales()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['locales' => [[]]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "currencies" values is not a scalar
     */
    public function testChannelUpdateWithNonScalarCurrencies()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['currencies' => ['EUR', []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "conversion_units" values is not a scalar
     */
    public function testChannelUpdateWithNonScalarConversionUnits()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['conversion_units' => ['weight' => 'GRAM', 'display_diagonal' => []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "code" expects a scalar as data, "array" given.
     */
    public function testChannelUpdateWithNonScalarCode()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['code' => []]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "category_tree" expects a scalar as data, "array" given.
     */
    public function testChannelUpdateWithNonScalarCategoryTree()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['category_tree' => []]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "category_tree" expects a valid code. The category does not exist, "category_tree" given.
     */
    public function testChannelUpdateWithUnknownCategoryTree()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['category_tree' => 'category_tree']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "currencies" expects a valid code. The currency does not exist, "YOLO" given.
     */
    public function testChannelUpdateWithUnknownCurrency()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['currencies' => ['YOLO']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "locales" expects a valid code. The locale does not exist, "YOLO" given.
     */
    public function testChannelUpdateWithUnknownLocale()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['locales' => ['YOLO']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "unknown_property" does not exist.
     */
    public function testChannelUpdateWithUnknownProperty()
    {
        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['unknown_property' => null]);
    }

    public function testSuccessChannelUpdate()
    {
        $channel = $this->createChannel();
        $data = [
            'code'             => 'ecommerce',
            'labels'           => [
                'fr_FR' => 'Tablette',
                'en_US' => 'Tablet',
            ],
            'locales'          => ['en_US', 'fr_FR'],
            'currencies'       => ['EUR', 'USD'],
            'category_tree'    => 'master',
            'conversion_units' => [
                'a_metric'                 => 'KILOWATT',
                'a_metric_without_decimal' => 'METER',
            ],
        ];

        $this->getUpdater()->update(
            $channel,
            $data
        );

        $this->assertSame($data['code'], $channel->getCode());
        $this->assertSame($data['labels']['fr_FR'], $channel->getTranslation('fr_FR')->getLabel());
        $this->assertSame($data['labels']['en_US'], $channel->getTranslation('en_US')->getLabel());
        $this->assertSame($data['locales'][0], $channel->getLocaleCodes()[0]);
        $this->assertSame($data['locales'][1], $channel->getLocaleCodes()[1]);
        $this->assertSame($data['currencies'][0], $channel->getCurrencies()[0]->getCode());
        $this->assertSame($data['currencies'][1], $channel->getCurrencies()[1]->getCode());
        $this->assertSame($data['category_tree'], $channel->getCategory()->getCode());
        $this->assertSame($data['conversion_units']['a_metric'], $channel->getConversionUnits()['a_metric']);
        $this->assertSame($data['conversion_units']['a_metric_without_decimal'], $channel->getConversionUnits()['a_metric_without_decimal']);
    }

    /**
     * @return ChannelInterface
     */
    protected function createChannel()
    {
        return $this->get('pim_catalog.factory.channel')->create();
    }

    /**
     * @return ObjectUpdaterInterface
     */
    protected function getUpdater()
    {
        return $this->get('pim_catalog.updater.channel');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}

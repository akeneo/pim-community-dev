<?php

namespace AkeneoTest\Pim\Channel\Integration\Channel;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

class UpdaterIntegration extends TestCase
{
    public function testUpdateObjectInChannelUpdater()
    {
        $this->expectException(InvalidObjectException::class);
        $this->expectExceptionMessage('Expects a "Akeneo\Channel\Component\Model\ChannelInterface", "stdClass" given.');

        $this->getUpdater()->update(new \stdClass(), []);
    }

    public function testChannelUpdateWithNullLabels()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "labels" expects an array as data, "NULL" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['labels' => null]);
    }

    public function testChannelUpdateWithNullLocales()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "locales" expects an array as data, "NULL" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['locales' => null]);
    }

    public function testChannelUpdateWithNullCurrencies()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "currencies" expects an array as data, "NULL" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['currencies' => null]);
    }

    public function testChannelUpdateWithNullConversionUnits()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "conversion_units" expects an array as data, "NULL" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['conversion_units' => null]);
    }

    public function testChannelUpdateWithNonScalarLabels()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "labels" values is not a scalar');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['labels' => ['en_US' => []]]);
    }

    public function testChannelUpdateWithNonScalarLocales()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "locales" values is not a scalar');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['locales' => [[]]]);
    }

    public function testChannelUpdateWithNonScalarCurrencies()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "currencies" values is not a scalar');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['currencies' => ['EUR', []]]);
    }

    public function testChannelUpdateWithNonScalarConversionUnits()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "conversion_units" values is not a scalar');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['conversion_units' => ['weight' => 'GRAM', 'display_diagonal' => []]]);
    }

    public function testChannelUpdateWithNonScalarCode()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "code" expects a scalar as data, "array" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['code' => []]);
    }

    public function testChannelUpdateWithNonScalarCategoryTree()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "category_tree" expects a scalar as data, "array" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['category_tree' => []]);
    }

    public function testChannelUpdateWithUnknownCategoryTree()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "category_tree" expects a valid code. The category does not exist, "category_tree" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['category_tree' => 'category_tree']);
    }

    public function testChannelUpdateWithUnknownCurrency()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "currencies" expects a valid code. The currency does not exist, "YOLO" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['currencies' => ['YOLO']]);
    }

    public function testChannelUpdateWithUnknownLocale()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "locales" expects a valid code. The locale does not exist, "YOLO" given.');

        $channel = $this->createChannel();

        $this->getUpdater()->update($channel, ['locales' => ['YOLO']]);
    }

    public function testChannelUpdateWithUnknownProperty()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Property "unknown_property" does not exist.');

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
     * @return \Akeneo\Channel\Component\Model\ChannelInterface
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
        return $this->catalog->useTechnicalCatalog();
    }
}

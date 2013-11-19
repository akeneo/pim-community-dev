<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\ChannelNormalizer;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelNormalizer
     */
    protected $normalizer;

    /**
     * @var string
     */
    protected $format;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new ChannelNormalizer();
        $this->format     = 'json';
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     * @static
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'json', true),
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'csv', false),
            array('stdClass', 'json', false),
            array('stdClass', 'csv', false)
        );
    }

    /**
     * Test related method
     * @param mixed   $class
     * @param string  $format
     * @param boolean $isSupported
     *
     * @dataProvider getSupportNormalizationData
     */
    public function testSupportNormalization($class, $format, $isSupported)
    {
        $data = $this->getMock($class);

        $this->assertSame($isSupported, $this->normalizer->supportsNormalization($data, $format));
    }

    /**
     * Data provider for testing normalize method
     * @return array
     * @static
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'       => 'channel_code',
                    'label'      => 'channel_label',
                    'currencies' => array('EUR', 'USD'),
                    'locales'    => array('fr_FR', 'en_US'),
                    'category'   => 'My_Tree'
                )
            )
        );
    }

    /**
     * Test normalize method
     * @param array $data
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $data)
    {
        $channel = $this->createChannel($data);

        $this->assertEquals(
            $data,
            $this->normalizer->normalize($channel, 'csv')
        );
    }

    /**
     * Create a channel
     * @param array $data
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function createChannel(array $data)
    {
        $channel = new Channel();
        $channel->setCode($data['code']);
        $channel->setLabel($data['label']);

        foreach ($data['currencies'] as $currencyCode) {
            $currency = $this->createCurrency($currencyCode);
            $channel->addCurrency($currency);
        }

        foreach ($data['locales'] as $localeCode) {
            $locale = $this->createLocale($localeCode);
            $channel->addLocale($locale);
        }

        $category = $this->createCategory($data['category']);
        $channel->setCategory($category);

        return $channel;
    }

    /**
     * Create a currency
     * @param string $currencyCode
     * @return \Pim\Bundle\ImportExportBundle\Normalizer\Currency
     */
    protected function createCurrency($currencyCode)
    {
        $currency = new Currency();
        $currency->setCode($currencyCode);

        return $currency;
    }

    /**
     * Create a locale
     * @param string $localeCode
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function createLocale($localeCode)
    {
        $locale = new Locale();
        $locale->setCode($localeCode);

        return $locale;
    }

    /**
     * Create a category
     * @param string $categoryCode
     * @return \Pim\Bundle\CatalogBundle\Entity\Category
     */
    protected function createCategory($categoryCode)
    {
        $category = new Category();
        $category->setCode($categoryCode);
        $category->setParent(null);

        return $category;
    }
}

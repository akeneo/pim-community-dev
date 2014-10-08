<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\TransformBundle\Normalizer\Structured\ChannelNormalizer;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new ChannelNormalizer();
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'json', true),
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'csv', false),
            array('stdClass', 'json', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'csv', false)
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'             => 'channel_code',
                    'label'            => 'channel_label',
                    'currencies'       => array('EUR', 'USD'),
                    'locales'          => array('fr_FR', 'en_US'),
                    'category'         => 'My_Tree',
                    'conversion_units' => 'weight: KILOGRAM, washing_temperature: ',
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return Channel
     */
    protected function createEntity(array $data)
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
        $channel->setConversionUnits(
            array(
                'weight'              => 'KILOGRAM',
                'washing_temperature' => null,
            )
        );

        return $channel;
    }

    /**
     * Create a currency
     * @param string $currencyCode
     *
     * @return Currency
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
     *
     * @return Locale
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
     *
     * @return Category
     */
    protected function createCategory($categoryCode)
    {
        $category = new Category();
        $category->setCode($categoryCode);
        $category->setParent(null);

        return $category;
    }
}

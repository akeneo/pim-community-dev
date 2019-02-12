<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCompletenessPerChannelAndLocaleIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->updateChannel('ecommerce', [
            'category_tree' => 'master',
            'currencies'    => ['USD'],
            'locales'       => ['fr_FR', 'en_US'],
            'labels' => [
                'fr_FR' => 'Ecommerce FR',
                'en_US' => 'Ecommerce US',
            ]
        ]);

        $this->createChannel([
            'code'          => 'mobile',
            'category_tree' => 'master',
            'currencies'    => ['USD'],
            'locales'       => ['en_US'],
            'labels' => [
                'fr_FR' => 'Mobile FR',
                'en_US' => 'Mobile US',
            ]
        ]);

        $this->createCategory(['code' => 'shoes', 'parent' => 'master']);

        $this->createAttribute([
            'code'              => 'name',
            'type'              => AttributeTypes::TEXT,
            'available_locales' => ['fr_FR', 'en_US'],
            'localizable'       => true,
            'scopable'          => true,
            'labels'            => [
                'fr_FR' => 'French name',
                'en_US' => 'English label',
            ],
        ]);

        $this->createAttribute([
            'code'              => 'description',
            'type'              => 'pim_catalog_textarea',
            'available_locales' => ['fr_FR', 'en_US'],
            'localizable'       => true,
            'scopable'          => true,
            'labels'            => [
                'fr_FR' => 'French description',
                'en_US' => 'English description',
            ],
        ]);

        $this->createFamily([
            'code'        => 'family_for_complete',
            'attributes'  => ['sku', 'name', 'description'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'name'],
                'mobile' => ['sku', 'name'],

            ]
        ]);

        $this->createFamily([
            'code'        => 'family_for_incomplete',
            'attributes'  => ['sku', 'name', 'description'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'name', 'description'],
                'mobile' => ['sku', 'name', 'description']
            ]
        ]);

        $this->createProducts(5, 5);
    }

    public function test_complete_completeness_widget()
    {
        $localeCompletenessesEcommerce = [
            new LocaleCompleteness('English (United States)', 5),
            new LocaleCompleteness('French (France)', 5)
        ];
        $localeCompletenessesMobile = [
            new LocaleCompleteness('English (United States)', 5)
        ];
        $channelCompletenesses = [
            new ChannelCompleteness('Ecommerce US', 10, 10, $localeCompletenessesEcommerce),
            new ChannelCompleteness('Mobile US', 5, 10, $localeCompletenessesMobile)
        ];
        $completenessWidget = new CompletenessWidget($channelCompletenesses);

        $translationLocale = $this->get('pim_user.context.user')->getCurrentLocaleCode();
        $results = $this->get('akeneo.pim.enrichment.follow_up.completeness_widget_query')->fetch($translationLocale);

        $this->assertSame($completenessWidget->toArray(), $results->toArray());
    }

    protected function createProducts($numberComplete, $numberIncomplete)
    {
        for ($i = 0; $i < $numberComplete; $i++) {
            $this->createProduct('product_complete_'.$i, [
                'family' => 'family_for_complete',
                'categories' => ['shoes'],
                'values' => [
                    'name'       => [
                        ['data' => 'name_ecom_fr_'.$i, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                        ['data' => 'name_ecom_us_'.$i, 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'name_mobile_us_'.$i, 'locale' => 'en_US', 'scope' => 'mobile']
                    ],
                    'description' => [
                        ['data' => 'descr_ecom_fr_'.$i, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                        ['data' => 'descr_ecom_us_'.$i, 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'descr_mobile_us_'.$i, 'locale' => 'en_US', 'scope' => 'mobile']
                    ]
                ]
            ]);
        }
        for ($i = 0; $i < $numberIncomplete; $i++) {
            $this->createProduct('product_incomplete_'.$i, [
                'family' => 'family_for_incomplete',
                'categories' => ['shoes'],
                'values' => [
                    'name'       => [
                        ['data' => 'name_mobile_US_'.$i, 'locale' => 'en_US', 'scope' => 'mobile']
                    ]
                ]
            ]);
        }
    }

    protected function createChannel(array $data = []): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    protected function updateChannel($code, array $data = []): ChannelInterface
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

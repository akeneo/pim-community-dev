<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp\GetCompletenessPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
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
    /** @var GetCompletenessPerChannelAndLocale */
    private $getCompletenessPerChannelAndLocale;

    public function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
        $this->getCompletenessPerChannelAndLocale = $this->get('akeneo.pim.enrichment.follow_up.completeness_widget_query');
    }

    public function test_complete_completeness_widget()
    {
        $this->givenSomeChannels();
        $this->andACategory();
        $this->andSomeAttributes();
        $this->andAFamily();
        $this->andSomeProducts();

        $results = $this->whenTheCompletenessForAllChannelsAndTheCurrentLocaleIsFetched();

        $this->thenTheCalculatedCompletenessesShouldBeCorrect($results);
    }

    private function createChannel(array $data = []): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    private function updateChannel($code, array $data = []): ChannelInterface
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

    private function givenSomeChannels(): void
    {
        $this->updateChannel(
            'ecommerce',
            [
                'category_tree' => 'master',
                'currencies'    => ['USD'],
                'locales'       => ['fr_FR', 'en_US'],
                'labels'        => [
                    'fr_FR' => 'Ecommerce FR',
                    'en_US' => 'Ecommerce US',
                ]
            ]
        );
        $this->createChannel([
            'code'          => 'mobile',
            'category_tree' => 'master',
            'currencies'    => ['USD'],
            'locales'       => ['en_US'],
            'labels'        => []
        ]
        );
    }

    private function andSomeAttributes(): void
    {
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
        ]
        );
    }

    private function andAFamily(): void
    {
        $this->createFamily([
            'code'                   => 'a_family',
            'attributes'             => ['sku', 'name', 'description'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'name'],
                'mobile'    => ['sku', 'name'],
            ]
        ]);
    }

    private function andACategory(): void
    {
        $this->createCategory(['code' => 'shoes', 'parent' => 'master']);
    }

    private function whenTheCompletenessForAllChannelsAndTheCurrentLocaleIsFetched(): CompletenessWidget
    {
        $translationLocale = $this->get('pim_user.context.user')->getCurrentLocaleCode();
        $result = $this->getCompletenessPerChannelAndLocale->fetch($translationLocale);

        return $result;
    }

    /**
     * @param CompletenessWidget $actualCompletenessWidget
     *
     */
    private function thenTheCalculatedCompletenessesShouldBeCorrect(CompletenessWidget $actualCompletenessWidget): void
    {
        $localeCompletenessesEcommerce = [
            new LocaleCompleteness('English (United States)', 8),
            new LocaleCompleteness('French (France)', 6)
        ];
        $localeCompletenessesMobile = [
            new LocaleCompleteness('English (United States)', 10)
        ];
        $channelCompletenesses = [
            new ChannelCompleteness('ecommerce', 5, 10, $localeCompletenessesEcommerce, [
                'en_US' => 'Ecommerce US',
                'fr_FR' => 'Ecommerce FR'
            ]),
            new ChannelCompleteness('mobile', 10, 10, $localeCompletenessesMobile, [
                'en_US' => null,
                'fr_FR' => null,
            ])
        ];
        $expectedCompletenessWidget = new CompletenessWidget($channelCompletenesses);
        $this->assertSame($expectedCompletenessWidget->toArray(), $actualCompletenessWidget->toArray());
    }

    private function andSomeProducts(): void
    {
        // All the 10 products are complete on the channel "mobile"
        $createProductIntentsBase = [
            new SetFamily('a_family'),
            new SetCategories(['shoes']),
            new SetTextareaValue('description', 'mobile', 'en_US', 'descr_mobile_us'),
            new SetTextValue('name', 'mobile', 'en_US', 'name_mobile_US'),
        ];

        // 5 products complete on all locales for the channel "ecommerce"
        for ($i = 0; $i < 5; $i++) {
            $this->createProduct('product_complete_'.$i, \array_merge($createProductIntentsBase, [
                new SetTextValue('name', 'ecommerce', 'fr_FR', 'name_ecom_fr_'.$i),
                new SetTextValue('name', 'ecommerce', 'en_US', 'name_ecom_us_'.$i),
            ]));
        }

        // 5 incomplete products for the channel "ecommerce"
        $this->createProduct('product_incomplete_1', $createProductIntentsBase);
        // with 3 products complete only for "en_US"
        $this->createProduct('product_incomplete_2', \array_merge($createProductIntentsBase, [
            new SetTextValue('name', 'ecommerce', 'en_US', 'name_ecom_us'),
        ]));
        $this->createProduct('product_incomplete_3', \array_merge($createProductIntentsBase, [
            new SetTextValue('name', 'ecommerce', 'en_US', 'name_ecom_us'),
        ]));
        $this->createProduct('product_incomplete_4', \array_merge($createProductIntentsBase, [
            new SetTextValue('name', 'ecommerce', 'en_US', 'name_ecom_us'),
        ]));
        // and with 1 product complete only for "fr_FR"
        $this->createProduct('product_incomplete_5', \array_merge($createProductIntentsBase, [
            new SetTextValue('name', 'ecommerce', 'fr_FR', 'name_ecom_fr'),
        ]));
    }
}

<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;

/**
 * Test the ProductAndProductModelQueryBuilder can return both product and product models in a smart way.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilderIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{

    /**
     * @group critical
     */
    public function testNoFilterAndSortIdentifier()
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'default_locale' => 'en_US',
                'default_scope'  => 'ecommerce',
                'limit'          => 19,
            ]
        );
        $pqb->addSorter('identifier', Directions::DESCENDING);

        $result = $pqb->execute();

        $this->assert(
            $result,
            [
                'watch',
                'zeus',
                'vulcanus',
                'venus',
                'terminus',
                'stock',
                'stilleto',
                'securitas',
                'quirinus',
                'poseidon',
                'portunus',
                'plain',
                'model-tshirt-unique-size',
                'model-tshirt-unique-color-kurt',
                'model-tshirt-divided',
                'model-running-shoes',
                'model-braided-hat',
                'model-biker-jacket',
                'moccasin',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testIdentifierFilter()
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'limit' => 19,
            ]
        );

        $pqb->addFilter(
            'identifier',
            Operators::IN_LIST,
            ['watch', 'model-tshirt-unique-size', 'tshirt-unique-size-crimson-red']
        );

        $result = $pqb->execute();

        $this->assert(
            $result,
            [
                'watch',
                'model-tshirt-unique-size',
                'tshirt-unique-size-crimson-red',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testIdFilter()
    {
        $productId = IdEncoder::encode(
            IdEncoder::PRODUCT_TYPE,
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('watch')->getId()
        );
        $variantProductId = IdEncoder::encode(
            IdEncoder::PRODUCT_TYPE,
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('tshirt-unique-size-crimson-red')->getId()
        );
        $productModelId = IdEncoder::encode(
            IdEncoder::PRODUCT_MODEL_TYPE,
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tshirt-unique-size')->getId()
        );

        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'limit' => 19,
            ]
        );

        $pqb->addFilter(
            'id',
            Operators::IN_LIST,
            [$productId, $variantProductId, $productModelId]
        );

        $result = $pqb->execute();

        $this->assert(
            $result,
            [
                'watch',
                'model-tshirt-unique-size',
                'tshirt-unique-size-crimson-red',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchTshirtInDescription()
    {
        $result = $this->executeFilter([['description', Operators::CONTAINS, 'Divided slim T-shirt with 2 buttons']]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided',
                'model-tshirt-unique-color-kurt',
                'model-tshirt-unique-size',
            ]
        );
    }

    /**
     * Simple search request that will return a mixed results of:
     * - VariantProducts (running-shoes-*)
     * - SubProductModel (model-tshirt-divided-crimson-red)
     * - RootProductModel (model-tshirt-unique-color)
     *
     * This mixed result is explained by the fact that the attribute "color" is not set at the same level within those 3
     * family variants.
     *
     * @group critical
     */
    public function testSearchColorRed()
    {
        $result = $this->executeFilter([['color', Operators::IN_LIST, ['crimson_red']]]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided-crimson-red',
                'model-tshirt-unique-color-kurt',
                'tshirt-unique-size-crimson-red',
                'running-shoes-xxs-crimson-red',
                'running-shoes-m-crimson-red',
                'running-shoes-xxxl-crimson-red',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchColorGrey()
    {
        $result = $this->executeFilter([['color', Operators::IN_LIST, ['battleship_grey']]]);

        $this->assert($result, ['model-tshirt-divided-battleship-grey', 'model-braided-hat']);
    }

    /**
     * @group critical
     */
    public function testSearchColorBlue()
    {
        $result = $this->executeFilter([['color', Operators::IN_LIST, ['navy_blue']]]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided-navy-blue',
                'tshirt-unique-size-navy-blue',
                'running-shoes-xxs-navy-blue',
                'running-shoes-m-navy-blue',
                'running-shoes-xxxl-navy-blue',
                'watch',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchSizeXXS()
    {
        $result = $this->executeFilter([['size', Operators::IN_LIST, ['xxs']]]);

        $this->assert(
            $result,
            [
                'tshirt-divided-battleship-grey-xxs',
                'tshirt-divided-navy-blue-xxs',
                'tshirt-divided-crimson-red-xxs',
                'tshirt-unique-color-kurt-xxs',
                'model-running-shoes-xxs',
                'biker-jacket-leather-xxs',
                'biker-jacket-polyester-xxs',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchSize3XL()
    {
        $result = $this->executeFilter([['size', Operators::IN_LIST, ['xxxl']]]);

        $this->assert(
            $result,
            [
                'tshirt-divided-battleship-grey-xxxl',
                'tshirt-divided-crimson-red-xxxl',
                'tshirt-divided-navy-blue-xxxl',
                'tshirt-unique-color-kurt-xxxl',
                'braided-hat-xxxl',
                'model-running-shoes-xxxl',
                'biker-jacket-leather-xxxl',
                'biker-jacket-polyester-xxxl',
            ]
        );
    }

    /**
     * Search request with 2 different attributes.
     *
     * Given those 2 attributes and a family variant (tree),
     * the search should return the documents which:
     * - level is the lowest between the levels set of those attributes
     * - and satisfy both conditions of the search.
     *
     * Ex: when searching for color=battleship-grey and size=s,
     *
     * We can see that the only products that satisfy those conditions are:
     * - The tshirt products and tshirt models with color battleship-grey and size s
     * - In the "clothing_color_size" family variant, size is defined at the product leve while color is defined at the
     *   subProductModel level. So we show the documents that belongs to the lowest involved. here level products.
     *
     * @group critical
     */
    public function testSearchColorGreyAndSizeXXS()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['battleship_grey']],
                ['size', Operators::IN_LIST, ['xxs']],
            ]
        );

        $this->assert($result, ['tshirt-divided-battleship-grey-xxs']);
    }

    /**
     * @group critical
     */
    public function testSearchColorGreyAndSize3XL()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['battleship_grey']],
                ['size', Operators::IN_LIST, ['xxxl']],
            ]
        );

        $this->assert($result, ['tshirt-divided-battleship-grey-xxxl', 'braided-hat-xxxl']);
    }

    /**
     * @group critical
     */
    public function testSearchColorGreyAndDescriptionTshirt()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['battleship_grey']],
                ['description', Operators::CONTAINS, 'T-shirt'],
            ]
        );

        $this->assert($result, ['model-tshirt-divided-battleship-grey']);
    }

    /**
     * @group critical
     */
    public function testSearchMaterialCotton()
    {
        $result = $this->executeFilter([['material', Operators::IN_LIST, ['cotton']]]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided-battleship-grey',
                'model-tshirt-divided-crimson-red',
                'model-tshirt-unique-color-kurt',
                'model-tshirt-unique-size',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchMaterialLeather()
    {
        $result = $this->executeFilter([['material', Operators::IN_LIST, ['leather']]]);

        $this->assert(
            $result,
            [
                'model-running-shoes',
                'model-biker-jacket-leather',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchSize3XLColorWhite()
    {
        $result = $this->executeFilter(
            [
                ['size', Operators::IN_LIST, ['xxxl']],
                ['color', Operators::IN_LIST, ['antique_white']],
            ]
        );

        $this->assert(
            $result,
            ['running-shoes-xxxl-antique-white', 'biker-jacket-polyester-xxxl', 'biker-jacket-leather-xxxl']
        );
    }

    /**
     * @group critical
     */
    public function testRedCotton()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['crimson_red']],
                ['material', Operators::IN_LIST, ['cotton']],
            ]
        );

        $this->assert(
            $result,
            [
                'model-tshirt-divided-crimson-red',
                'model-tshirt-unique-color-kurt',
                'tshirt-unique-size-crimson-red',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testNotGreyAndXXS()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::NOT_IN_LIST, ['battleship_grey']],
                ['size', Operators::IN_LIST, ['xxs']],
            ]
        );

        $this->assert(
            $result,
            [
                'tshirt-divided-navy-blue-xxs',
                'tshirt-divided-crimson-red-xxs',
                'tshirt-unique-color-kurt-xxs',
                'running-shoes-xxs-antique-white',
                'running-shoes-xxs-navy-blue',
                'running-shoes-xxs-crimson-red',
                'biker-jacket-leather-xxs',
                'biker-jacket-polyester-xxs',
                'model-running-shoes-xxs',
            ]
        );
    }

    /**
     * @group critical
     */
    public function testNotGreyAndNotXXSAndPolyester()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::NOT_IN_LIST, ['battleship_grey']],
                ['size', Operators::NOT_IN_LIST, ['xxs']],
                ['material', Operators::IN_LIST, ['polyester']],
            ]
        );

        $this->assert(
            $result,
            [
                'tshirt-divided-navy-blue-m',
                'tshirt-divided-navy-blue-l',
                'tshirt-divided-navy-blue-xxxl',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-xxxl',
                'model-biker-jacket-polyester',
                'model-tshirt-divided-navy-blue',
            ]
        );
    }

    /**
     * This test is failing, probably due to a problem with the batch size.
     * With a batch size of 50 it's ok, but not with a batch size of 10.
     *
     * It is skipped but it should be fix by the support.
     *
     * @group skip
     * @group critical
     */
    public function testStatusOnProductWithoutParent()
    {
        $result = $this->executeFilter(
            [
                ['enabled', Operators::EQUALS, true]
            ]
        );

        $this->assert(
            $result,
            [
                '1111111111',
                '1111111112',
                '1111111113',
                '1111111114',
                '1111111115',
                '1111111116',
                '1111111120',
                '1111111121',
                '1111111122',
                '1111111123',
                '1111111124',
                '1111111125',
                '1111111126',
                '1111111127',
                '1111111128',
                '1111111129',
                '1111111130',
                '1111111131',
                '1111111132',
                '1111111133',
                '1111111134',
                '1111111135',
                '1111111136',
                '1111111137',
                '1111111138',
                '1111111139',
                '1111111140',
                '1111111141',
                '1111111142',
                '1111111143',
                '1111111144',
                '1111111145',
                '1111111146',
                '1111111147',
                '1111111148',
                '1111111149',
                '1111111150',
                '1111111151',
                '1111111152',
                '1111111153',
                '1111111154',
                '1111111155',
                '1111111156',
                '1111111157',
                '1111111158',
                '1111111159',
                '1111111160',
                '1111111161',
                '1111111162',
                '1111111163',
                '1111111164',
                '1111111165',
                '1111111166',
                '1111111167',
                '1111111168',
                '1111111169',
                '1111111170',
                '1111111210',
                '1111111211',
                '1111111212',
                '1111111213',
                '1111111214',
                '1111111215',
                '1111111216',
                '1111111217',
                '1111111218',
                '1111111219',
                '1111111220',
                '1111111221',
                '1111111222',
                '1111111223',
                '1111111224',
                '1111111225',
                '1111111226',
                '1111111227',
                '1111111228',
                '1111111229',
                '1111111230',
                '1111111231',
                '1111111232',
                '1111111233',
                '1111111234',
                '1111111235',
                '1111111236',
                '1111111237',
                '1111111238',
                '1111111239',
                '1111111240',
                '1111111241',
                '1111111242',
                '1111111243',
                '1111111244',
                '1111111245',
                '1111111246',
                '1111111247',
                '1111111248',
                '1111111249',
                '1111111250',
                '1111111251',
                '1111111252',
                '1111111253',
                '1111111254',
                '1111111255',
                '1111111256',
                '1111111257',
                '1111111258',
                '1111111259',
                '1111111260',
                '1111111261',
                '1111111262',
                '1111111267',
                '1111111268',
                '1111111269',
                '1111111270',
                '1111111271',
                '1111111272',
                '1111111273',
                '1111111274',
                '1111111275',
                '1111111276',
                '1111111277',
                '1111111278',
                '1111111279',
                '1111111280',
                '1111111281',
                '1111111282',
                '1111111283',
                '1111111284',
                '1111111285',
                '1111111286',
                '1111111287',
                '1111111288',
                '1111111289',
                '1111111290',
                '1111111291',
                '1111111292',
                '1111111293',
                '1111111294',
                '1111111295',
                '1111111296',
                '1111111297',
                '1111111298',
                '1111111299',
                '1111111300',
                '1111111301',
                '1111111302',
                '1111111303',
                '1111111304',
                '1111111305',
                '1111111306',
                '1111111307',
                '1111111308',
                '1111111309',
                '1111111310',
                '1111111311',
                '1111111312',
                '1111111313',
                '1111111314',
                '1111111315',
                '1111111316',
                '1111111317',
                '1111111318',
                '1111111319',
                'biker-jacket-leather-m',
                'biker-jacket-leather-xxs',
                'biker-jacket-leather-xxxl',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-xxs',
                'biker-jacket-polyester-xxxl',
                'braided-hat-m',
                'braided-hat-xxxl',
                'running-shoes-m-antique-white',
                'running-shoes-m-crimson-red',
                'running-shoes-m-navy-blue',
                'running-shoes-xxs-antique-white',
                'running-shoes-xxs-crimson-red',
                'running-shoes-xxs-navy-blue',
                'running-shoes-xxxl-antique-white',
                'running-shoes-xxxl-crimson-red',
                'running-shoes-xxxl-navy-blue',
                'tshirt-divided-battleship-grey-l',
                'tshirt-divided-battleship-grey-m',
                'tshirt-divided-battleship-grey-xxs',
                'tshirt-divided-battleship-grey-xxxl',
                'tshirt-divided-crimson-red-l',
                'tshirt-divided-crimson-red-m',
                'tshirt-divided-crimson-red-xxs',
                'tshirt-divided-crimson-red-xxxl',
                'tshirt-divided-navy-blue-l',
                'tshirt-divided-navy-blue-m',
                'tshirt-divided-navy-blue-xxs',
                'tshirt-divided-navy-blue-xxxl',
                'tshirt-unique-color-kurt-l',
                'tshirt-unique-color-kurt-m',
                'tshirt-unique-color-kurt-xxs',
                'tshirt-unique-color-kurt-xxxl',
                'tshirt-unique-size-crimson-red',
                'tshirt-unique-size-electric-yellow',
                'tshirt-unique-size-navy-blue',
                'watch'
            ]
        );
    }

    /**
     * @group critical
     */
    public function testSearchCategoriesMenWithoutIncludingChildren()
    {
        $result = $this->executeFilter(
            [
                ['categories', Operators::IN_LIST, ['master_men']],
            ]
        );

        $this->assert($result, []);
    }

    /**
     * @group critical
     */
    public function testSearchCategoriesMenIncludingChildren()
    {
        $result = $this->executeFilter(
            [
                ['categories', Operators::IN_LIST, [
                    'master_men',
                    'master_men_blazers',
                    'master_men_blazers_deals',
                    'master_men_pants',
                    'master_men_pants_shorts',
                    'master_men_pants_jeans',
                    'master_men_shoes',
                    'tshirts',
                ]
                ],
            ]
        );

        $this->assert($result, [
            '1111111305',
            '1111111306',
            '1111111307',
            '1111111308',
            '1111111312',
            '1111111313',
            '1111111314',
            '1111111315',
            '1111111316',
            'amor',
            'apollon',
            'ares',
            'bacchus',
            'brogueshoe',
            'brooksblue',
            'caelus',
            'climbingshoe',
            'converseblack',
            'conversered',
            'derby',
            'dionysos',
            'dressshoe',
            'elegance',
            'galesh',
            'hades',
            'hefaistos',
            'hermes',
            'jack',
            'moccasin',
            'model-biker-jacket',
            'model-running-shoes',
            'model-tshirt-divided',
            'model-tshirt-unique-color-kurt',
            'model-tshirt-unique-size',
            'plain',
            'portunus',
            'poseidon',
            'quirinus',
            'venus',
            'zeus',
        ]);
    }

    /**
     * @group critical
     */
    public function testSearchColorRedAndCategoryMenIncludingChildrenCategories()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['crimson_red']],
                [
                    'categories',
                    Operators::IN_LIST,
                    [
                        'master_men_blazers',
                        'master_men_blazers_deals',
                        'master_men_pants',
                        'master_men_pants_shorts',
                        'master_men_pants_jeans',
                        'master_men_shoes',
                        'tshirts',
                        'master_men',
                    ],
                ],
            ]
        );

        $expectedResult = [
            // Are in category 'Shoes' (which is a child of the 'Men' category) and have Color = 'Crimson red'
            'running-shoes-xxs-crimson-red',
            'running-shoes-m-crimson-red',
            'running-shoes-xxxl-crimson-red',

            // Are in category 'T-shirts' (which is a child of the 'Men' category) and have Color = 'Crimson red'
            'tshirt-unique-size-crimson-red',
            'model-tshirt-divided-crimson-red',
            'model-tshirt-unique-color-kurt'
        ];
        $this->assert($result, $expectedResult);
    }

    public function testSearchColorRedInASubCategoryAndHisChildren()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['yellow']],
                [
                    'categories',
                    Operators::IN_CHILDREN_LIST,
                    [
                        'master_men_blazers'
                    ],
                ],
            ]
        );

        $expectedResult = [
            // Are in category "deals" (wich is a child of "Men">"Blazers" categorie) and have Color = "yellow"
            '1111111213',
            '1111111214',
            '1111111215',
            '1111111216',
            'apollon_yellow',
            'ares_yellow'
        ];
        $this->assert($result, $expectedResult);
    }

}

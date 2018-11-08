<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAndProductModelDatasourceSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $rowNormalizer,
        Query\FetchProductAndProductModelRows $query
    ) {
        $this->beConstructedWith($objectManager, $pqbFactory, $rowNormalizer, $query);

        $this->setParameters(['dataLocale' => 'fr_FR']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelDatasource::class);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement(DatasourceInterface::class);
        $this->shouldImplement(ParameterizableInterface::class);
    }

    function it_fetch_product_and_product_model_rows(
        $pqbFactory,
        $rowNormalizer,
        $query,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $rowCursor
    ) {
        $config = [
            'displayed_attribute_ids' => [1, 2],
            'attributes_configuration' => [
                'attribute_1' => [
                    'id' => 1,
                    'code' => 'attribute_1'
                ],
                'attribute_2' => [
                    'id' => 2,
                    'code' => 'attribute_2'
                ],
                'attribute_3' => [
                    'id' => 3,
                    'code' => 'attribute_3'
                ],
            ],
            'locale_code' => 'fr_FR',
            'scope_code' => 'ecommerce',
            'association_type_id' => 2,
            'current_group_id' => 3,
            PagerExtension::PER_PAGE_PARAM => 15
        ];

        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 15,
            'from'                  => 0,
            'default_locale'        => 'fr_FR',
            'default_scope'         => 'ecommerce',
        ])->willReturn($pqb);

        $pqb->getQueryBuilder()->shouldBeCalledTimes(1);

         $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            null,
            null,
            90,
            1,
            'parent_code',
            new ValueCollection()
        );
        $query->__invoke(new Query\FetchProductAndProductModelRowsParameters(
            $pqb->getWrappedObject(),
            ['attribute_1', 'attribute_2'],
            'ecommerce',
            'fr_FR',
            0
        ))->willReturn(new Rows([$row], 1));
        $this->process($datagrid, $config);

        $rowNormalizer->normalize($row, 'datagrid', [
            'locales'       => ['fr_FR'],
            'channels'      => ['ecommerce'],
            'data_locale'   => 'fr_FR',
            'association_type_id' => 2,
            'current_group_id' => 3
        ])->willReturn([
            'identifier'   => 'identifier',
            'family'       => 'family label',
            'groups'       => 'group_1,group_2',
            'enabled'      => true,
            'values'       => [],
            'created'      => '2018-05-23T15:55:50+01:00',
            'updated'      => '2018-05-23T15:55:50+01:00',
            'label'        => 'data',
            'image'        => null,
            'completeness' => 90,
            'document_type' => 'product',
            'technical_id' => 1,
            'id'           => 1,
            'search_id' => 'product_1',
            'is_checked' => true,
            'complete_variant_product' => [],
            'parent' => 'parent_code',
        ]);

        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(2);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 1);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(1);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
    }

    public function getMatchers()
    {
        return [
            'beAnArrayOfInstanceOf' => function (array $subjects, $class) {
                foreach ($subjects as $subject) {
                    if (!$subject instanceof $class) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
